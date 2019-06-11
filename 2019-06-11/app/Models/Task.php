<?php

declare(strict_types=1);

namespace App\Models;

class Task extends DefaultModel
{
    public $id;
    public $user_name;
    public $email;
    public $text;
    public $status = 0;

    const STATUS_NEW = 0;
    const STATUS_COMPLETED = 1;

    public function getFullInfo(): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user_name,
            'email' => $this->email,
            'text' => $this->text,
            'status' => $this->status,
        ];
    }

    public static function getById(int $id)
    {
        $statement = self::db()->prepare('SELECT * FROM `tasks` WHERE `id` = ? LIMIT 1');
        $statement->execute([
            $id
        ]);
        if (!$statement->rowCount()) {
            return null;
        }
        $statement->setFetchMode(\PDO::FETCH_CLASS, self::class);
        $obj = $statement->fetch();
        return $obj;
    }

    public static function pagesCount(int $limit = 3): int
    {
        if ($limit === 0) {
            return 1;
        }
        $statement = self::db()->prepare('SELECT COUNT(*) FROM `tasks`');
        $statement->execute();
        $rows = (int)$statement->fetchColumn();
        return (int)(ceil($rows / $limit));
    }

    public static function getList(string $sortBy = 'id', int $limit = 3, int $page = 1): array
    {
        $availableSort = [
            'id',
            'user_name',
            'email',
            'status'
        ];

        // protect change sort and SQL Injection...
        if (!in_array($sortBy, $availableSort)) {
            $sortBy = 'id';
        }

        $start = (int)(($page - 1) * $limit);

        $prepare = self::db()->prepare("SELECT * FROM `tasks` ORDER BY `{$sortBy}` LIMIT {$start}, {$limit}");
        $prepare->setFetchMode(\PDO::FETCH_CLASS, self::class);
        $prepare->execute();

        return $prepare->fetchAll();
    }

    public function save()
    {
        // exists record
        if ($this->id) {
            $statement = self::db()->prepare('UPDATE `tasks` SET `user_name` = ?, `email` = ?, `text` = ?, `status` = ? WHERE `id` = ?');
            $statement->execute([
                $this->user_name,
                $this->email,
                $this->text,
                $this->status,
                $this->id,
            ]);
        } else {
            // new record
            $statement = self::db()->prepare('INSERT INTO `tasks` (`user_name`, `email`, `text`, `status`) VALUES (?, ?, ?, ?)');
            $statement->execute([
                $this->user_name,
                $this->email,
                $this->text,
                $this->status,
            ]);
        }
    }
}