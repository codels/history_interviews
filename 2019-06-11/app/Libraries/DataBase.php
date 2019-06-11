<?php

declare(strict_types=1);

namespace App\Libraries;

use PDO;

class DataBase
{
    private $connect = null;

    public function __construct(string $host, int $port, string $user, string $password, string $database, string $driver = 'mysql')
    {
        $dsn = $driver . ':host=' . $host;
        if ($port) {
            $dsn .= ';port=' . $port;
        }
        $dsn .= ';dbname=' . $database;
        $this->connect = new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    public function getConnect(): PDO
    {
        return $this->connect;
    }
}