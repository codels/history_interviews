<?php

// Строгая типизация
declare(strict_types=1);

// Подключаем интерфейсы
require_once 'face_finder_interfaces.php';
require_once 'database.php';

/**
 * Базовый класс для поиска, по умолчанию не работает
 * Class FaceFinder
 */
class FaceFinder implements FaceFinderInterface
{
    // Название базы даных
    const DATABASE_NAME = 'face_finder';

    // Название таблицы
    const TABLE_NAME = 'faces';

    // Количество отдаваемых записей при поиске
    const SEARCH_LIMIT = 5;

    // Количество последних просматриваемых записей
    const SEARCH_IN_LAST_COUNT = 10000;

    /**
     * Конструктор класса FaceFinder должен подключаться к MySQL и создавать (если не
     * существует) базу данных с названием face_finder. В этой БД должна быть 1 таблица
     * для хранения лиц, добавленных через класс FaceFinder. Эта таблица также создается
     * в конструкторе класса (если есть необходимость в этом). Название и структура
     * таблицы выбирается вами.
     */
    public function __construct()
    {
        // Создание базы, если её нет
        DataBase::pdo()->query('
            CREATE SCHEMA IF NOT EXISTS `' . self::DATABASE_NAME . '` DEFAULT CHARACTER SET utf8
        ');

        // Переключится на базу данных
        DataBase::pdo()->exec('USE `' . self::DATABASE_NAME . '`');

        /**
         * Создание таблицы, если её нет
         * id - int, поидее можно ограничиться smallint(5) в рамках задания, но я тестировал с большими значениями
         * race - tinyint(3), так как у нас максимальное значение 100
         * emotion - smallint(4), наше ограничение в 1000
         * oldness - smallint(4), наше ограничение в 1000
         * cluster_index - int, тестировал создание кластеров как 100 штук так и 100000
         *
         * Ключ id
         * Индекс cluster_index, для более быстрой выборки, чтобы не проходить через все строки
         *
         * Движок таблицы InnoDB, в условиях задачи есть конечно момент что выборка идет чаще
         * чем вставка, можно рассмотреть вариант MyISAM, так как на выборку показывает лучше результат
         * но есть свои минусы. В общем тут выбор надо делать из более точных условий.
         */
        DataBase::pdo()->query('
            CREATE TABLE IF NOT EXISTS `' . self::TABLE_NAME . '` (
                `id`  int UNSIGNED NOT NULL AUTO_INCREMENT ,
                `race`  tinyint(3) UNSIGNED NOT NULL ,
                `emotion`  smallint(4) UNSIGNED NOT NULL ,
                `oldness`  smallint(4) UNSIGNED NOT NULL ,
                `cluster_index`  int UNSIGNED NOT NULL ,
                PRIMARY KEY (`id`),
                KEY `cluster_index` (`cluster_index`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');
    }

    /**
     * Finds 5 most similar faces in DB.
     * If the specified face is new (id=0),
     * then it will be saved to DB.
     *
     * @param FaceInterface $face Face to find and save (if id=0)
     * @return FaceInterface[] List of 5 most similar faces,
     * including the searched one
     */
    public function resolve(FaceInterface $face): array
    {
        return [];
    }

    /**
     * Удаление всех лиц из базы данных и сброс ключа
     * реализовано дословно, альтернативный вариант удалить всю таблицу.
     *
     * Removes all faces in DB and (!) reset faces id sequence
     */
    public function flush(): void
    {
        // Удалить все данные
        DataBase::pdo()->query('DELETE FROM `' . self::TABLE_NAME . '`');

        // Сбросить счетчик
        DataBase::pdo()->query('ALTER TABLE `' . self::TABLE_NAME . '` AUTO_INCREMENT=1');
    }

    /**
     * Добавить лицо в БД
     *
     * @param FaceInterface $face Данные лица
     * @param int $clusterIndex Индекс кластера, добавлено отдельной переменной так как
     *      делал несколько алгоритмов с разной работой
     * @return int $id Возвращаем номер добавленного лица
     * @throws Exception Можем словить исключение если запрос не выполнен или строка не добавлена
     *      ловлю исключение сейчас только при выполнении всей программы, можно подойти более
     *      гибко и предусмотреть работу разных функций на основе попыток или обработки ситуации
     */
    public function addFace(FaceInterface $face, int $clusterIndex = 0): int
    {
        // Задаем переменную для PDO, так как используем несколько раз
        $pdo = DataBase::pdo();

        // Подготовка запроса
        $statement = $pdo->prepare('
            INSERT INTO `' . self::TABLE_NAME . '`
            (`race`, `emotion`, `oldness`, `cluster_index`)
            VALUES
            (?, ?, ?, ?)
        ');

        // Если не выполнился запрос
        if (!$statement->execute([
            $face->getRace(),
            $face->getEmotion(),
            $face->getOldness(),
            $clusterIndex
        ])) {
            throw new Exception('Query incorrect');
        }

        // Если нет добавленных строк
        if (!$statement->rowCount()) {
            throw new Exception('Query incorrect');
        }

        // Функция всегда отдает строку, поэтому меняем формат данных
        $id = (int)$pdo->lastInsertId();

        // Вернуть добавленный номер
        return $id;
    }

    /**
     * Получить максимальный ID лица, используется в основном чтобы ограничивать выборку по
     * последним 10000 лиц, показался как самый быстрый вариант, чтобы не делать сложный запрос
     *
     * @return int $id Номер лица
     */
    public function getMaxFaceId(): int
    {
        $statement = DataBase::pdo()->prepare('
            SELECT `id`
            FROM `' . static::TABLE_NAME . '`
            ORDER BY `id` DESC
            LIMIT 0, 1
        ');
        if ($statement->execute() && $statement->rowCount()) {
            return (int)$statement->fetchColumn();
        }
        return 0;
    }
}

/**
 * Базовый класс лиц
 * Class Face
 *
 * Данные сделаны как protected (можно и private) на случай расширения, не public
 * так как есть геттеры для получения значений в интерфейсе. Поэтому мой мозг решил что
 * лучше будет ограничить доступ.
 *
 * @property int $id Номер
 * @property int $race Раса
 * @property int $emotion Эмоция
 * @property int $oldness Старость
 */
class Face implements FaceInterface
{
    protected $id = 0;
    protected $race = 0;
    protected $emotion = 0;
    protected $oldness = 0;

    /**
     * Face constructor.
     * @param int $race Раса
     * @param int $emotion Эмоция
     * @param int $oldness Старость
     * @param int $id Номер, если 0 то создаем точку в БД
     */
    public function __construct(int $race = 0, int $emotion = 0, int $oldness = 0, int $id = 0)
    {
        // Не заменяем данные если объект был создан с помощью PDO
        // и в конструктор отправлены все 0, на случай если должно быть 0
        // значения по умолчанию для переменных выставлены на 0
        if ($race) {
            $this->race = $race;
        }

        if ($emotion) {
            $this->emotion = $emotion;
        }

        if ($oldness) {
            $this->oldness = $oldness;
        }

        if ($id) {
            $this->id = $id;
        }
    }

    /**
     * Returns face id or 0, if face is new
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns race parameter: from 0 to 100.
     */
    public function getRace(): int
    {
        return $this->race;
    }

    /**
     * Returns face emotion level: from 0 to 1000.
     */
    public function getEmotion(): int
    {
        return $this->emotion;
    }

    /**
     * Returns face oldness level: from 0 to 1000.
     */
    public function getOldness(): int
    {
        return $this->oldness;
    }
}
