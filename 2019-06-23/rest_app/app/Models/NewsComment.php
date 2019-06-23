<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Models;

/**
 * Класс для работы с комментариями новостей
 * Class NewsComment
 * @package App\Libraries
 *
 * @property int $id Уникальный номер комментария
 * @property int $news_id Номер новости
 * @property string $comment Текст комментария
 * @property string $user_name Имя пользователя, вернее кем он представился))
 * @property string $created_at Дата создания комментария
 * @property string $updated_at Дата изменения комментария
 * @property string $deleted_at Дата удаления комментария
 */
class NewsComment extends Model
{
    public $id;
    public $news_id;
    public $comment;
    public $user_name;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    /**
     * Получить комментарии по номеру новости
     * @param int $newsId
     * @return self[]
     */
    public static function getCommentsByNewsId(int $newsId): array
    {
        $db = self::pdo();
        $statement = $db->prepare('SELECT `id`, `news_id`, `comment`, `user_name`, `created_at`, `updated_at`, `deleted_at` FROM `news_comments` WHERE `news_id` = ? AND `deleted_at` IS NULL ORDER BY `id` DESC');
        $statement->execute([$newsId]);
        // Если нет данных, отдаем пустой массив
        if (!$statement->rowCount()) {
            return [];
        }
        // Преобразовывать строки в объекты этого класса
        $statement->setFetchMode(\PDO::FETCH_CLASS, __CLASS__);
        return $statement->fetchAll();
    }

    /**
     * Получить комментарий по номеру
     * @param int $id Номер комментария
     * @return NewsComment|null Объект комментария
     */
    public static function getById(int $id): ?self
    {
        $db = self::pdo();
        $statement = $db->prepare('SELECT `id`, `news_id`, `comment`, `user_name`, `created_at`, `updated_at`, `deleted_at` FROM `news_comments` WHERE `id` = ? LIMIT 1');
        $statement->execute([$id]);
        // Если нет данных, отдаем NULL
        if (!$statement->rowCount()) {
            return null;
        }
        // Преобразовывать строки в объекты этого класса
        $statement->setFetchMode(\PDO::FETCH_CLASS, __CLASS__);
        return $statement->fetch();
    }

    /**
     * Удаление комментария
     * @param bool $isSoftDelete Мягкое удаление =) Без удаления строчки, или удалить строку в БД
     * @return bool Было ло ли выполнено удаление
     */
    public function remove(bool $isSoftDelete = true): bool
    {
        $db = self::pdo();
        if ($isSoftDelete) {
            $currentTime = date('Y-m-d H:i:s');
            $params = [$currentTime, $this->id];
            // Тут ещё можно заменить на SET `deleted_at` = CURRENT_TIMESTAMP() но будут свои нюансы
            $statement = $db->prepare('UPDATE `news_comments` SET `deleted_at` = ? WHERE `id` = ? LIMIT 1');
        } else {
            $statement = $db->prepare('DELETE FROM `news_comments` WHERE `id` = ? LIMIT 1');
            $params = [$this->id];
        }
        $statement->execute($params);
        $result = ($statement->rowCount() === 1);
        if ($result && $isSoftDelete && isset($currentTime)) {
            $this->deleted_at = $currentTime;
        }
        return $result;
    }

    /**
     * Сохранить комментарий
     * @return bool
     */
    public function save(): bool
    {
        $db = self::pdo();
        $currentTime = date('Y-m-d H:i:s');
        // exists record
        if ($this->id) {
            $this->updated_at = $currentTime;
            $statement = $db->prepare('UPDATE `news_comments` SET `news_id` = ?, `comment` = ?, `user_name` = ?, `created_at` = ?, `updated_at` = ?, `deleted_at` = ? WHERE `id` = ? LIMIT 1');
            $statement->execute([
                $this->news_id,
                $this->comment,
                $this->user_name,
                $this->created_at,
                $this->updated_at,
                $this->deleted_at,
                $this->id,
            ]);
        } else {
            $this->created_at = $currentTime;
            $this->updated_at = $this->created_at;
            // new record
            $statement = $db->prepare('INSERT INTO `news_comments` (`news_id`, `comment`, `user_name`, `created_at`, `updated_at`, `deleted_at`) VALUES (?, ?, ?, ?, ?, ?)');
            $statement->execute([
                $this->news_id,
                $this->comment,
                $this->user_name,
                $this->created_at,
                $this->updated_at,
                $this->deleted_at,
            ]);
            $this->id = (int)$db->lastInsertId();
        }
        return ($statement->rowCount() === 1);
    }
}