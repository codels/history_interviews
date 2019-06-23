<?php

// Строгая типизация
declare(strict_types=1);

namespace App\Models;

/**
 * Класс для работы с новостями
 * Class News
 * @package App\Libraries
 *
 * @property int $id Уникальный номер новости
 * @property string $text Текст новости
 * @property string $created_at Дата создания новости
 * @property string $updated_at Дата изменения новости
 * @property string $deleted_at Дата удаления новости
 */
class News extends Model
{
    public $id;
    public $text;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    /**
     * Получить все текущие новости, кроме удаленных.
     * @return News[] Массив объектов новостей
     */
    public static function getAll(): array
    {
        $db = self::pdo();
        $statement = $db->prepare('SELECT `id`, `text`, `created_at`, `updated_at`, `deleted_at` FROM `news` WHERE `deleted_at` IS NULL ORDER BY `id` DESC');
        $statement->execute();
        // Если нет данных, отдаем пустой массив
        if (!$statement->rowCount()) {
            return [];
        }
        // Преобразовывать строки в объекты этого класса
        $statement->setFetchMode(\PDO::FETCH_CLASS, __CLASS__);
        return $statement->fetchAll();
    }

    /**
     * Получить новость по номеру
     * @param int $id Номер новости
     * @return News|null Объект новости
     */
    public static function getById(int $id): ?self
    {
        $db = self::pdo();
        $statement = $db->prepare('SELECT `id`, `text`, `created_at`, `updated_at`, `deleted_at` FROM `news` WHERE `id` = ? LIMIT 1');
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
     * Удаление новости
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
            $statement = $db->prepare('UPDATE `news` SET `deleted_at` = ? WHERE `id` = ? LIMIT 1');
        } else {
            $statement = $db->prepare('DELETE FROM `news` WHERE `id` = ? LIMIT 1');
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
     * Сохранить новость
     * @return bool
     */
    public function save(): bool
    {
        $db = self::pdo();
        $currentTime = date('Y-m-d H:i:s');
        // exists record
        if ($this->id) {
            $this->updated_at = $currentTime;
            $statement = $db->prepare('UPDATE `news` SET `text` = ?, `created_at` = ?, `updated_at` = ?, `deleted_at` = ? WHERE `id` = ? LIMIT 1');
            $statement->execute([
                $this->text,
                $this->created_at,
                $this->updated_at,
                $this->deleted_at,
                $this->id,
            ]);
        } else {
            $this->created_at = $currentTime;
            $this->updated_at = $this->created_at;
            // new record
            $statement = $db->prepare('INSERT INTO `news` (`text`, `created_at`, `updated_at`, `deleted_at`) VALUES (?, ?, ?, ?)');
            $statement->execute([
                $this->text,
                $this->created_at,
                $this->updated_at,
                $this->deleted_at,
            ]);
            $this->id = (int)$db->lastInsertId();
        }
        return ($statement->rowCount() === 1);
    }

    /**
     * Получить комментарии новости
     * @return NewsComment[]
     */
    public function getComments(): array
    {
        return NewsComment::getCommentsByNewsId((int)$this->id);
    }
}