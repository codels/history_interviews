<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Controllers\DefaultController;
use PDO;

class Application
{
    private static $instance = null;

    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // protect init
    private function __construct()
    {
        session_start();
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            $this->isAdmin = true;
        }
    }

    // запустить приложение
    public function execute(?string $route = null): bool
    {
        $controllerName = 'main';
        $actionName = 'index';

        if (null === $route) {
            $route = 'main/index';
        }

        $routeExploded = explode('/', $route);
        if (isset($routeExploded[0])) {
            $controllerName = $routeExploded[0];
        }

        if (isset($routeExploded[1])) {
            $actionName = $routeExploded[1];
        }

        // first symbol to upper in controller name
        $controllerName[0] = strtoupper($controllerName[0]);

        // is valid controller name
        if (!preg_match('/^([a-zA-Z]+)$/', $controllerName)) {
            return false;
        }

        // is valid action name
        if (!preg_match('/^([a-zA-Z]+)$/', $actionName)) {
            return false;
        }

        $className = '\\App\\Controllers\\' . $controllerName . 'Controller';

        if (!class_exists($className)) {
            return false;
        }

        $controllerObject = new $className($this);
        if (!($controllerObject instanceof DefaultController)) {
            return false;
        }

        $actionName.= 'Action';

        if (!method_exists($controllerObject, $actionName)) {
            return false;
        }

        $controllerObject->$actionName();

        return true;
    }

    private $database = null;

    public function db(): PDO
    {
        if (!($this->database instanceof PDO)) {
            $configDatabase = Config::getConfig('database');
            $this->database = (new DataBase(
                $configDatabase['host'],
                $configDatabase['port'],
                $configDatabase['user'],
                $configDatabase['password'],
                $configDatabase['database']
            ))->getConnect();
        }
        return $this->database;
    }

    // сделал переменную админ или нет чтобы не писать систему прав и модели пользователей
    private $isAdmin = false; // temp var for check admin or not

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setAdmin(bool $isAdmin)
    {
        if ($isAdmin) {
            $_SESSION['is_admin'] = $isAdmin;
        } else {
            unset($_SESSION['is_admin']);
        }
    }

    // чтобы не писать миграции ради одной таблицы сделал временную функцию.
    public function install()
    {
        $this->db()->query('DROP TABLE IF EXISTS `tasks`');
        $this->db()->query('
            CREATE TABLE `tasks` (
                `id`  int UNSIGNED NOT NULL AUTO_INCREMENT ,
                `user_name`  varchar(255) NULL ,
                `email`  varchar(255) NULL ,
                `text`  text NULL ,
                `status`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
                PRIMARY KEY (`id`)
            )
        ');
    }
}