<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Request;
use App\Libraries\Response;
use App\Models\News;

/**
 * Контроллер для работы с новостями
 * Class NewsController
 * @package App\Controllers
 */
class NewsController extends Controller
{

    /**
     * Получить все новости
     */
    public function showAction()
    {
        $news = News::getAll();
        $this->_response->setParams($news);
    }

    /**
     * Получить информацию о конкретной новости
     * @param int $id Номер новости
     */
    public function getAction($id)
    {
        $news = News::getById((int)$id);

        // Если новость не найдена то 404 ошибка
        if (null === $news) {
            $this->_response->setStatusCode(Response::STATUS_NOT_FOUND);
            return;
        }

        $this->_response->setParams($news);
    }

    /**
     * Удалить новость
     * @param int $id Номер новости
     */
    public function removeAction($id)
    {
        $news = News::getById((int)$id);

        // Если новость не найдена то 404 ошибка
        if (null === $news) {
            $this->_response->setStatusCode(Response::STATUS_NOT_FOUND);
            return;
        }

        if (!$news->remove()) {
            $this->_response->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        // Возвразаемый ответ по умолчанию null и будет отдан пустой объект {}
        // В некоторых framework'ах, такой ответ считается более правильным
        // Но больще всего тут важен факт 200 статуса
    }


    /**
     * Обновить новость
     * @param int $id Номер новости
     * @throws \Exception
     */
    public function updateAction($id)
    {
        $news = News::getById((int)$id);

        // Если новость не найдена то 404 ошибка
        if (null === $news) {
            $this->_response->setStatusCode(Response::STATUS_NOT_FOUND);
            return;
        }

        // Проверка был ли передан текст
        if (!Request::isExists('text')) {
            $this->_response
                ->setParams(['error' => 'news_text_not_found'])
                ->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        $text = Request::getString('text');
        // Если ничего не изменилось
        if ($news->text === $text) {
            $this->_response->setParams($news);
            return;
        }

        $news->text = $text;

        if (!$news->save()) {
            throw new \Exception("Cannot save news(" . json_encode($news) . ")");
        } else {
            $this->_response->setParams($news);
        }
    }

    /**
     * Создать новость
     */
    public function createAction()
    {
        $news = new News();

        // Проверка был ли передан текст
        if (!Request::isExists('text')) {
            $this->_response
                ->setParams(['error' => 'news_text_not_found'])
                ->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        $news->text = Request::getString('text');
        if (empty($news->text)) {
            $this->_response
                ->setParams(['error' => 'news_text_is_empty'])
                ->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        if (!$news->save()) {
            throw new \Exception("Cannot save news(" . json_encode($news) . ")");
        } else {
            // 201 Status Created
            $this->_response->setParams($news)->setStatusCode(Response::STATUS_CREATED);
        }
    }
}
