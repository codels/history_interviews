<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Request;
use App\Libraries\Response;
use App\Models\News;
use App\Models\NewsComment;

/**
 * Контроллер для работы с комментариями новостей
 * Class NewsCommentController
 * @package App\Controllers
 */
class NewsCommentController extends Controller
{
    /**
     * Получить все комментарии новости
     * @param int $newsId Номер новости
     */
    public function showAction($newsId)
    {
        $comments = NewsComment::getCommentsByNewsId((int)$newsId);
        $this->_response->setParams($comments);
    }

    /**
     * Удалить коммментарий у новости
     * @param int $newsId Номер новости
     * @param int $commentId Номер комментария
     */
    public function removeAction($newsId, $commentId)
    {
        $comment = NewsComment::getById((int)$commentId);

        // Если комментарий не найден или запрос не от той новости то 404 ошибка
        if (null === $comment || (int)$comment->news_id !== (int)$newsId) {
            $this->_response->setStatusCode(Response::STATUS_NOT_FOUND);
            return;
        }

        if (!$comment->remove()) {
            $this->_response->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        // Возвразаемый ответ по умолчанию null и будет отдан пустой объект {}
        // В некоторых framework'ах, такой ответ считается более правильным
        // Но больще всего тут важен факт 200 статуса
    }

    /**
     * Опубликовать комментарий у новости
     * @param int $newsId Номер новости
     * @throws \Exception
     */
    public function createAction($newsId)
    {
        $comment = new NewsComment();
        $comment->news_id = (int)$newsId;

        $news = News::getById($comment->news_id);

        // Если новость не найдена
        if (null === $news) {
            $this->_response
                ->setParams(['error' => 'news_not_found'])
                ->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        // Проверка был ли передан текст
        if (!Request::isExists('comment')) {
            $this->_response
                ->setParams(['error' => 'comment_text_not_found'])
                ->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        $comment->comment = Request::getString('comment');
        if (empty($comment->comment)) {
            $this->_response
                ->setParams(['error' => 'comment_text_is_empty'])
                ->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        if (!Request::isExists('user_name')) {
            $this->_response
                ->setParams(['error' => 'user_name_not_found'])
                ->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        $comment->user_name = Request::getString('user_name');
        if (empty($comment->user_name)) {
            $this->_response
                ->setParams(['error' => 'user_name_is_empty'])
                ->setStatusCode(Response::STATUS_BAD_REQUEST);
            return;
        }

        if (!$comment->save()) {
            throw new \Exception("Cannot save news(" . json_encode($comment) . ")");
        } else {
            // 201 Status Created
            $this->_response->setParams($comment)->setStatusCode(Response::STATUS_CREATED);
        }
    }
}
