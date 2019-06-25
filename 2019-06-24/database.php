<?php

// Строгая типизация
declare(strict_types=1);

/**
 * Класс для работы с базой данных, Singleton для красоты... наверное...
 * Class DataBase
 *
 * @property PDO|null $_connect Соединение
 */
class DataBase
{
    const DRIVER = 'mysql';
    const SERVER_HOST = 'localhost';
    const SERVER_PORT = 3306;
    const USER_NAME = 'test_user';
    const USER_PASSWORD = 'test_password';

    private static $_instance = null;
    private $_connect = null;

    /**
     * Получить экземпляр класса
     * @return DataBase
     */
    public static function getInstance(): self
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Приватный конструктор для защиты от создания экземпляра из вне
     * DataBase constructor.
     */
    private function __construct()
    {
        // Подготавлияем конфиг для соединения с БД
        $dsn = self::DRIVER . ':host=' . self::SERVER_HOST;
        if (self::SERVER_PORT) {
            $dsn .= ';port=' . self::SERVER_PORT;
        }
        $this->_connect = new PDO(
            $dsn,
            self::USER_NAME,
            self::USER_PASSWORD,
            [
                // Конфигурация подключения
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Отдавать исключения при ошибках
                PDO::ATTR_EMULATE_PREPARES => false, // Отключаем эмуляцию, с ней типы данных не верно передаются из БД
                PDO::ATTR_STRINGIFY_FETCHES => false, // Отключить конвертацию чисел в строки
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Формат по умолчанию для отдаваемых данных
            ]
        );
    }

    /**
     * Получить драйвер для работы с БД
     * @return PDO
     */
    public static function pdo(): PDO
    {
        $instance = self::getInstance();
        return $instance->_connect;
    }
}
