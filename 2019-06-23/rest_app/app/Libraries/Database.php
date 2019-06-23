<?php

declare(strict_types=1);

namespace App\Libraries;

use PDO;

/**
 * Класс для работы с базой данных
 * Class Database
 * @package App\Libraries
 *
 * @param PDO $_connect Соединение с базой данных
 */
class Database
{
    private $_connect = null;

    /**
     * Database constructor.
     * @param string $user Пользователь
     * @param string $password Пароль
     * @param string $database База данных
     * @param string $host Хост
     * @param int $port Порт
     * @param string $driver Драйвер
     */
    public function __construct(string $user, string $password, string $database,
                                string $host = 'localhost', int $port = 3306, string $driver = 'mysql')
    {
        // Подготавлияем конфиг для соединения с БД
        $dsn = $driver . ':host=' . $host;
        if ($port) {
            $dsn .= ';port=' . $port;
        }
        $dsn .= ';dbname=' . $database;
        $this->_connect = new PDO(
            $dsn,
            $user,
            $password,
            [
                // Ошибки отдавать как исключения
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    /**
     * Получить PDO соединение
     * @return PDO
     */
    public function pdo(): PDO
    {
        return $this->_connect;
    }
}