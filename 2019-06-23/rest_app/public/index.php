<?php

// Строгая типизация
declare(strict_types=1);

try {
    // Подключаем авто загрузчик классов
    require_once __DIR__ . '/../app/bootstrap.php';

    // Получить экземпляр приложения
    $app = \App\Libraries\Application::getInstance();

    // Получаем запрашиваемый путь и метод
    // не выносил в отдельную функцию, так как встречаются практики хранения пути в разных переменных
    $requestMethod = 'GET';
    $path = '';
    if (isset($_SERVER)) {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $requestMethod = (string)$_SERVER['REQUEST_METHOD'];
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $path = (string)$_SERVER['REQUEST_URI'];
            // Отсекаем данные после ? в url
            $pathExplode = explode('?', $path);
            $path = $pathExplode[0];
        }
    }
    // Выполнить запрашиваемый путь
    $response = $app->execute($requestMethod, $path);
    // Проверяем ответ, хотя он вывалится при попытке отдать не верный тип данных
    // так как у функции строгий тип данных для ответа
    if ($response instanceof \App\Libraries\Response) {
        $response->display();
    } else {
        throw new Exception("Incorrect response from application path '{$path}'");
    }

} catch (Exception $e) {
    // Логируем ошибку
    error_log($e->getMessage());
    error_log($e->getTraceAsString());

    // Генерируем сами ответ, так как возможно проблема в загрузчике классов,
    // а значит нам не доступен класс Response

    // Проверяем, успел ли сервер отправить заголовки
    if (!headers_sent()) {
        // Ответ в виде JSON с кодировкой UTF-8
        header('Content-type:application/json;charset=utf-8');
    }
    // Меням статус ответа
    http_response_code(500); // Internal Server Error
    // Json сообщение об ошибке для REST приложения
    echo json_encode(['error' => 'fatal_error']);
}
