<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Application;
use App\Libraries\Request;

class DefaultController
{
    protected $app = null;
    protected $view = 'default';
    protected $params = [];

    public function __construct(Application $app)
    {
        $this->app = $app;

        // default params for view
        $this->params['is_admin'] = $this->app->isAdmin();
        if (Request::isExists('error')) {
            $this->params['error'] = Request::getString('error');
        }
    }

    public function isExistsParam($key)
    {
        return isset($this->params[$key]);
    }

    public function setParam($key, $value)
    {
        if (is_string($key) || is_numeric($key)) {
            $this->params[$key] = $value;
        }
    }

    public static function protectValue($value)
    {
        if (is_string($value)) {
            return htmlentities($value);
        } else {
            return $value;
        }
    }

    public function getParam($key, $protect = true)
    {
        if (is_string($key) || is_numeric($key)) {
            if (isset($this->params[$key])) {
                $value = $this->params[$key];
                if ($protect) {
                    return self::protectValue($value);
                } else {
                    return $value;
                }
            }
        }

        return null;
    }

    public function display(?string $view = null)
    {
        if (null === $view) {
            $view = $this->view;
        }
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        }
    }

    public function indexAction()
    {
        $this->display();
    }

    public function redirect(string $url)
    {
        header('Location: ' . $url);
    }
}