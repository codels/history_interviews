<?php

declare(strict_types=1);

namespace App\Libraries;

abstract class Config
{
    private static $configs = [];

    public static function getConfig(string $config): ?array
    {
        $config = ucfirst($config);

        if (array_key_exists($config, self::$configs)) {
            return self::$configs[$config];
        }

        $path = __DIR__ . '/../Configs';

        // is valid file name
        if (!preg_match('/^([a-zA-Z_0-9]+)$/', $config)) {
            return null;
        }

        $path .= '/' . $config . '.php';

        if (!file_exists($path)) {
            return null;
        }

        $result = include $path;

        if (!is_array($result)) {
            return null;
        }

        self::$configs[$config] = $result;

        return $result;
    }
}