<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Controllers;

/**
 * Контроллер Привет мир))
 * Class HelloWorldController
 * @package App\Controllers
 */
class HelloWorldController extends Controller
{
    /**
     * Вызываемый метод по умолчанию
     * В маршрутах указан путь /
     */
    public function indexAction()
    {
        $this->_response->setParams(['message' => 'Hello! This is news REST api.']);
    }
}
