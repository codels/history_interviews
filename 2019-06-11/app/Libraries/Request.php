<?php

declare(strict_types=1);

namespace App\Libraries;

abstract class Request
{
    public static function get($key, $default = null)
    {
        if (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }
        return $default;
    }

    public static function isExists($key)
    {
        return isset($_REQUEST[$key]);
    }

    public static function getString($key, $default = '')
    {
        return (string)self::get($key, $default);
    }

    public static function getInt($key, $default = 0)
    {
        return (int)self::get($key, $default);
    }
}