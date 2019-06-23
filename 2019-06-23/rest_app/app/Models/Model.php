<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Models;

use App\Libraries\Application;

/**
 * Базовый класс моделей
 * Class Model
 * @package App\Libraries
 */
class Model
{
    /**
     * Получить соединение с БД
     * @return \PDO
     */
    protected static function pdo()
    {
        return Application::getInstance()->db()->pdo();
    }

    // Можно было бы поидее сделать общую функцию сохранения с перебором параметров, но ради двух классов решил
    // не усложнять этот момент, да и везде используют AR/ORM для таких целей...
}