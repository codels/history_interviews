<?php

/**
 * Массив маршрутов в формате
 * Method RegExPath Controller::method
 */
return [
    // default
    ['get',     '/',                                'HelloWorldController::indexAction'],

    // news
    ['get',     '/news',                            'NewsController::showAction'],
    ['get',     '/news/([0-9]+)',                   'NewsController::getAction'],
    ['delete',  '/news/([0-9]+)',                   'NewsController::removeAction'],
    ['put',     '/news/([0-9]+)',                   'NewsController::updateAction'],
    ['post',    '/news',                            'NewsController::createAction'],

    // comments
    ['get',     '/news/([0-9]+)/comments',          'NewsCommentController::showAction'],
    ['delete',  '/news/([0-9]+)/comments/([0-9]+)', 'NewsCommentController::removeAction'],
    ['post',    '/news/([0-9]+)/comments',          'NewsCommentController::createAction'],

    // Установка таблиц
    ['get',     '/system_install',                  'SystemController::installAction'],

    // not found
    ['*',       '.*',                               'NotFoundController'],
];