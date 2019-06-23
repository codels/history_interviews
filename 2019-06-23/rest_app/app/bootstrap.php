<?php

// Строгая типизация
declare(strict_types=1);

// Объявялем свою функцию загрузки классов
if (spl_autoload_register(function (string $className) {
    // Авто загрузка классов только с символами от a до z, включая символ \, регистронезависимая проверка
    if (!preg_match('/^([a-z\\\]+)$/i', $className)) {
        return;
    }

    // App => app, Так как всё приложение находится в папке app, а именованная область App
    $className[0] = strtolower($className[0]);

    // Заменяем разделение namespace на директории
    $className = str_replace('\\', '/', $className);

    // Подключаем нужный файл единоразово
    $filePath = __DIR__ . '/../' . $className . '.php';
    if (file_exists($filePath) === true) {
        require_once $filePath;
    }
}) === false) {
    throw new Exception('Cannot register auto loader');
}