<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\Application;
use PDO;

class DefaultModel
{
    public static function db(): PDO
    {
        $app = Application::getInstance();
        return $app->db();
    }
}