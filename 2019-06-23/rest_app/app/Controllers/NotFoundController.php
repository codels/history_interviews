<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Response;

/**
 * Контроллер для случаев когда не удалось найти запрашиваемый метод
 * Class NotFoundController
 * @package App\Controllers
 */

class NotFoundController extends Controller
{
    /**
     * Метод выполняемый по умолчанию
     */
    public function indexAction()
    {
        $this->_response
            ->setParams(['error' => 'method not found'])
            ->setStatusCode(Response::STATUS_NOT_FOUND);
    }
}
