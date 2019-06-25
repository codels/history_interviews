<?php

// Строгая типизация
declare(strict_types=1);

// Используемый базовый класс
require_once 'face_finder_base_classes.php';

/**
 * Класс с использованием базы данных, если мы поддерживаем параллельные запросы
 *      и данные могут поступить с других приложений
 * Class FaceFinderInDataBase
 */
class FaceFinderInDataBase extends FaceFinder
{
    /**
     * Finds 5 most similar faces in DB.
     * If the specified face is new (id=0),
     * then it will be saved to DB.
     *
     * @param FaceInterface $face Face to find and save (if id=0)
     * @return FaceInterface[] List of 5 most similar faces,
     * including the searched one
     * @throws Exception
     */
    public function resolve(FaceInterface $face): array
    {
        // Создаем новую запись если нет ID
        if ($face->getId() === 0) {
            $id = $this->addFace($face);
            // Возможно стоило создать функцию по присвоению id
            // но проблема что мы передаем только интерфейс,
            // а в нем не указана данная функция, не знаю стоит ли нарушать условия задачи
            // изменяя интерфейс. Другой вариант, отдать создание строки модели Face...
            $newFace = new Face($face->getRace(), $face->getEmotion(), $face->getOldness(), $id);
        }

        $faces = $this->search(
            $face->getRace(),
            $face->getEmotion(),
            $face->getOldness(),
            (isset($newFace) ? self::SEARCH_LIMIT - 1 : self::SEARCH_LIMIT),
            (isset($newFace) ? $newFace->getId() : 0)
        );

        // Если есть новое лицо, добавляем его первым, как самое подходящее
        if (isset($newFace)) {
            array_unshift($faces, $newFace);
        }

        return $faces;
    }

    /**
     * Функция поиска
     * @param int $race Раса
     * @param int $emotion Эмоция
     * @param int $oldness Старость
     * @param int $count Количество искомых лиц
     * @param int $exceptionId Исключить из поиска номер
     * @return array
     */
    public function search(int $race, int $emotion, int $oldness, int $count = 5, int $exceptionId = 0): array
    {
        // Получаем ID, чтобы ограничить поиск среди последних 10000
        $maxId = $this->getMaxFaceId();

        // Убрали корень, так как мы тут не за вычисление длинны, а нам важен сам факт больше или меньше
        $statement = DataBase::pdo()->prepare('
            SELECT
                    `f`.`id`,
                    `f`.`race`,
                    `f`.`emotion`,
                    `f`.`oldness`,
                    (POW(`f`.`race` - ?, 2) + POW(`f`.`emotion` - ?, 2) + POW(`f`.`oldness` - ?, 2)) AS `l`
            FROM `'.self::TABLE_NAME.'` AS `f`
            WHERE `id` >= ? AND `id` <> ?
            ORDER BY `l` ASC
            LIMIT '.$count.'
        ');
        $statement->execute([$race, $emotion, $oldness, $maxId - self::SEARCH_IN_LAST_COUNT, $exceptionId]);
        $statement->setFetchMode(\PDO::FETCH_CLASS, 'Face');
        return $statement->fetchAll();
    }
}
