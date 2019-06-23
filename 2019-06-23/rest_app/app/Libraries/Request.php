<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Libraries;

/**
 * Класс для работы с поступающими данными и их фильтрацией
 * Class Request
 * @package App\Libraries
 */
class Request
{
    private static $_instance = null;

    protected $_requestData = null;

    /**
     * Получение экземляра класса
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Приватный конструктор, чтобы нельзя было создать экземпляр
     *
     * Request constructor.
     */
    private function __construct()
    {
        // Копируем данные чтобы не изменять $_REQUEST в случае следующей модернизации класса и подстановки других данных
        $this->_requestData = $_REQUEST;

        // Проверяем поступили ли JSON данные и дополняем их в поступившие данные
        if (self::isRequestJson()) {
            $input = file_get_contents('php://input');
            if ($input !== false && !empty($input)) {
                $json = json_decode($input, true);
                if (is_array($json)) {
                    $this->_requestData = array_merge($this->_requestData, $json);
                }
            }
        }
    }

    /**
     * Определяем запрос был с json телом или нет
     * @return bool
     */
    public static function isRequestJson(): bool
    {
        if (!empty($_SERVER['CONTENT_TYPE'])) {
            $items = explode(';', $_SERVER['CONTENT_TYPE']);
            foreach ($items as $item) {
                if (trim($item) === 'application/json') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Получить поступившую переменную
     * @param string $key Имя переменной
     * @param mixed $default Значение по умолчанию, если переменная не найдена
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $request = self::getInstance();
        if (isset($request->_requestData[$key])) {
            return $request->_requestData[$key];
        }
        return $default;
    }

    /**
     * Проверка на существование поступившей переменной
     * @param string $key Имя переменной
     * @return bool
     */
    public static function isExists(string $key): bool
    {
        $request = self::getInstance();
        return isset($request->_requestData[$key]);
    }

    /**
     * Получить строковую поступившую переменую
     * @param string $key Имя переменной
     * @param string $default Значение по умолчанию, если переменная не найдена
     * @return string
     */
    public static function getString(string $key, string $default = ''): string
    {
        return (string)self::get($key, $default);
    }

    /**
     * Получить числовую поступившую переменую
     * @param string $key Имя переменной
     * @param int $default Значение по умолчанию, если переменная не найдена
     * @return int
     */
    public static function getInt(string $key, int $default = 0): int
    {
        return filter_var(self::get($key, $default), FILTER_VALIDATE_INT);
    }

    /**
     * Получить булевую поступившую переменую
     * @param string $key Имя переменной
     * @param bool $default Значение по умолчанию, если переменная не найдена
     * @return bool
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(self::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }
}