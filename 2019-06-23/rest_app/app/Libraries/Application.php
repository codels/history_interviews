<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Libraries;

/**
 * Класс приложения, основан на паттерне Singleton, так как всего 1 приложение у нас может существовать
 *
 * Class Application
 * @package App\Libraries
 *
 * @param null|Application $instance Экземпляр класса
 */
class Application
{
    private static $_instance = null;

    private $_routes = [];
    private $_isEnabled = false;
    private $_config = [];
    private $_db = null;

    /**
     * Получение экземляра класса
     *
     * @return Application
     */
    public static function getInstance(): Application
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Приватный конструктор, чтобы нельзя было создать экземпляр
     *
     * Application constructor.
     */
    private function __construct()
    {
        // Загружаем список маршрутов
        $this->_routes = (array)include __DIR__ . '/../routers.php';

        // Загружаем конфигурацию
        $configFile = __DIR__ . '/../../config.ini';
        if (file_exists($configFile) === true) {
            $config = parse_ini_file($configFile, true);
            // Если файл был корректно распознан
            if (is_array($config)) {
                $this->_config = $config;
            }
        }

        // Передаем состояние приложения в переменную
        if (isset($this->_config['application'])) {
            if (isset($this->_config['application']['enable'])) {
                // Применяем фильтр, чтобы можно было упрощать формат данных в ini
                $this->_isEnabled = filter_var($this->_config['application']['enable'], FILTER_VALIDATE_BOOLEAN);
            }
        }
    }

    /**
     * Выполнить запрашиваемый путь. Подключить необходимый контроллер и выполнить запрашиваемые действия
     * @param string $requestMethod Запрашиваемый метод (POST, GET, PUT, ...)
     * @param string $path Запрашиваемые путь (/news, /comments, ...)
     * @return Response Ответ который можно отобразить
     * @throws \Exception не найденные контроллеры или методы
     */
    public function execute(string $requestMethod, string $path): Response
    {
        // Если приложение отключено
        if (!$this->isEnable()) {
            return new Response([
                'error' => 'application_disabled',
            ], Response::STATUS_SERVICE_UNAVAILABLE);
        }

        // Находим выполняемый контроллер, действие и переменные
        list($controllerName, $actionName, $params) = $this->getControllerAndAction($requestMethod, $path);

        if (!class_exists($controllerName)) {
            throw new \Exception("Controller '{$controllerName}' not found");
        }

        // Формируемый ответ клиенту
        $response = new Response();

        // Создаем экземпляр контроллера, передавая туда Response, в который будет генерироваться ответ
        $controller = new $controllerName($response);

        // Проверка существование метода у контроллера, так как будем его выполнять
        if (!method_exists($controller, $actionName)) {
            throw new \Exception("Method '{$controllerName}::{$actionName}' not found");
        }

        // Выполнить метод контроллера
        call_user_func_array([$controller, $actionName], $params);

        return $response;
    }

    /**
     * Ищем запускаемый контроллер и передаваемые ему параметры
     * @param string $requestMethod
     * @param string $path
     * @return array Возвращает в строгом порядке ИмяКонтроллера, ВыполняемыеМетод, Параметры
     * @throws \Exception Контроллер не найден
     */
    public function getControllerAndAction(string $requestMethod, string $path): array
    {
        // Делаем строку в нижний регистр, для едионого формата хранения в маршрутах
        $method = strtolower($requestMethod);

        // Поиск маршрута
        foreach ($this->_routes as $route) {
            // 0 - method
            // 1 - path
            // 2 - controller::method

            // Проверка структуры массива
            if (!is_array($route) || !isset($route[0]) || !isset($route[1]) || !isset($route[2])) {
                throw new \Exception('Incorrect route (' . json_encode($route) . ') in routes, need array with keys 0, 1, 2');
            }

            if ($route[0] !== '*' && $route[0] !== $method) {
                // Не подходящий метод
                continue;
            }

            // Создаем шаблон для поиска
            $routePath = str_replace('/', '\\/', $route[1]);
            $pattern = "/^{$routePath}$/i";
            $matches = []; // Найденные совпадения
            if (!preg_match($pattern, $path, $matches)) {
                // Не подходящий путь
                continue;
            }
            // Удаляем из совпадений строчку
            array_shift($matches);

            // Разделяем класс от метода
            $call = explode('::', $route[2]);
            return [
                'App\\Controllers\\' . $call[0], // class
                isset($call[1]) ? $call[1] : 'indexAction', // method
                $matches, // params
            ];
        }

        // Маршрут не найден
        throw new \Exception("Controller for method '{$requestMethod}' and path '{$path}' not found");
    }

    /**
     * Включено ли приложение
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->_isEnabled;
    }

    /**
     * Получение соединение с БД, возможно стоило создать отдельную фабрику по хранению сервисов...
     * @return Database
     */
    public function db(): Database
    {
        if (!($this->_db instanceof Database)) {
            $dbConfig = [
                'driver' => 'mysql',
                'host' => 'localhost',
                'port' => 3306,
                'user' => 'user',
                'password' => 'password',
                'database' => 'database',
            ];
            // Если в конфигурации есть данные для подключения, заменяем ими наши данные по умолчанию
            if (isset($this->_config['database'])) {
                $dbConfig = array_merge($dbConfig, $this->_config['database']);
            }
            $this->_db = new Database(
                (string)$dbConfig['user'],
                (string)$dbConfig['password'],
                (string)$dbConfig['database'],
                (string)$dbConfig['host'],
                (int)$dbConfig['port'],
                (string)$dbConfig['driver']
            );
        }
        return $this->_db;
    }
}