<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Libraries;

/**
 * Класс для формирования ответа клиенту
 * Class Response
 * @package App\Libraries
 *
 * @param int $_statusCode HTTP Status
 * @param mixed $_params Отдаваемый ответ клиенту
 */
class Response
{
    // success
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;

    // clients
    const STATUS_BAD_REQUEST = 400;
    const STATUS_NOT_FOUND = 404;

    // server
    const STATUS_INTERNAL_ERROR = 500;
    const STATUS_SERVICE_UNAVAILABLE = 503;

    private $_statusCode = self::STATUS_OK;
    private $_params = null;

    /**
     * Response constructor.
     * @param null $params Отдаваемый ответ клиенту
     * @param int $statusCode Статус ответа
     */
    public function __construct($params = null, int $statusCode = self::STATUS_OK)
    {
        $this->_statusCode = $statusCode;
        $this->_params = $params;
    }

    /**
     * Установить новый ответ клиенту
     * @param $params
     * @return Response
     */
    public function setParams($params): Response
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Установить новый статус
     * @param int $statusCode
     * @return Response
     */
    public function setStatusCode(int $statusCode): Response
    {
        $this->_statusCode = $statusCode;
        return $this;
    }

    /**
     * Вывод ответа клиенту
     */
    public function display(): void
    {
        header('Content-type:application/json;charset=utf-8');
        http_response_code($this->_statusCode);
        // В случаях когда REST приложению нужен пустой объект, а не пустая коллекция
        if (null === $this->_params) {
            echo '{}';
        } else {
            echo json_encode($this->_params);
        }
    }

    /**
     * Функция формирования ответа ошибки
     * @param string $error
     * @param int $code
     */
    public function setError(string $error, int $code = self::STATUS_BAD_REQUEST)
    {
        $this->setParams(['error' => $error])->setStatusCode($code);
    }
}