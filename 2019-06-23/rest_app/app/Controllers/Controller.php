<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Response;

/**
 * Базовый контроллер, от которого наследуются остальные, получаются доступ к объект ответа для клиента
 * Class Controller
 * @package App\Controllers
 *
 * @param Response $_response Ответ отдаваемый пользователю
 */
class Controller
{

    protected $_response = null;

    /**
     * Передаем объект который отвечает за формирование ответа
     * Controller constructor.
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->_response = $response;
    }
}
