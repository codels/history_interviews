<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Application;

/**
 * Контроллер системный =) лучше вызвать 1 раз и забыть
 * Class SystemController
 * @package App\Controllers
 */
class SystemController extends Controller
{
    /**
     * Функция установки таблиц, сделал так чтобы не создавать свою версию миграций и т.п.
     */
    public function installAction()
    {
        $pdo = Application::getInstance()->db()->pdo();

        // Удаляем таблицу новостей, если она уже есть
        $pdo->query('DROP TABLE IF EXISTS `news`');
        // Формируем таблицу новостей, индекс добавлен в text на случай поиска,
        // в рамках задания его можно и не добавлять, так как нет поиска по содержимому
        $pdo->query('
            CREATE TABLE `news` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `deleted_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              FULLTEXT KEY `text` (`text`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        // Удаляем таблицу комментариев, если она уже есть
        $pdo->query('DROP TABLE IF EXISTS `news_comments`;');
        // Формируем таблицу комментариев
        // возможно стоит дополнительно ограничить имя пользователя
        $pdo->query('
            CREATE TABLE `news_comments` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `news_id` int(10) unsigned NOT NULL,
              `comment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `deleted_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        // Можно сделать ещё генерацию fake data...
    }
}
