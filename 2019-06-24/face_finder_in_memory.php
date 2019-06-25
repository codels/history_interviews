<?php

// Строгая типизация
declare(strict_types=1);

// Используемый базовый класс
require_once 'face_finder_base_classes.php';

/**
 * Самый тупой способ в лоб по убиванию ОЗУ сервера...
 * Формируем массив связей для каждой добавляемого лица (точки)
 * Формируется всё это добро ужасно долго, поиск поидее должен быть быстрым
 *
 * Class FaceFinderInMemory
 *
 * @property array[] $_nearest Ближайшие точки к точке...
 * @property array[] $_data Так как мы тут за скорость, мы не храним объекты
 *      а делаем как быстрее и не читаемее, работает с массвами и индексами
 *      где 0 - id, 1 - race, 2 - emotion, 3 - oldness
 * @property int $_count Максимальный индекс
 */
class FaceFinderInMemory extends FaceFinder
{
    private $_nearest = [];
    private $_data = [];
    private $_count = 0;

    /**
     * Расширяем базовый контруктор, чтобы формировать массив связей при получении данных из БД
     * Можно конечно сохранить результаты близости точке в базе и ускорить это добро... по части загрузки
     *
     * FaceFinderInMemory constructor.
     */
    public function __construct()
    {
        // Делаем грязные дела от базового контруктора
        parent::__construct();

        $statement = DataBase::pdo()->prepare('SELECT `id`, `race`, `emotion`, `oldness` FROM `' . self::TABLE_NAME . '` ORDER BY `id` ASC');
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_NUM);
        $rows = $statement->fetchAll();

        // Добавляем и формируем информация о близости лиц (точек)
        foreach ($rows as &$row) {
            $this->_addToData($row[0], $row[1], $row[2], $row[3]);
        }
    }

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

            $this->_addToData($id, $face->getRace(), $face->getEmotion(), $face->getOldness());
        }

        $faces = $this->search(
            $face->getRace(),
            $face->getEmotion(),
            $face->getOldness(),
            (isset($newFace) ? self::SEARCH_LIMIT - 1 : self::SEARCH_LIMIT),
            (isset($newFace) ? $newFace->getId() : -1)
        );

        // Если есть новое лицо, добавляем его первым, как самое подходящее
        if (isset($newFace)) {
            array_unshift($faces, $newFace);
        }

        return $faces;
    }

    /**
     * Добавляем лицо в массив
     * @param int $id
     * @param int $race
     * @param int $emotion
     * @param int $oldness
     */
    private function _addToData(int $id, int $race, int $emotion, int $oldness)
    {
        // Добавляем новую запись в наше хранилище
        $this->_data[] = [$id, $race, $emotion, $oldness];

        // Создаем данные о близости для данного лица
        $this->_createNearest($this->_count);

        // Создаем данные о близости данного лица для других
        $this->_addNearest($this->_count);

        // Увеличиваем кол-во лиц
        $this->_count++;
    }

    /**
     * Добавляем информацию о новом лице для других лиц
     * @param int $index Индекс лица в массиве
     */
    private function _addNearest(int $index)
    {
        $currentFace = $this->_data[$index];
        foreach ($this->_data as $i => &$face) {
            // Пропускаем сами себя
            if ($i === $index) {
                continue;
            }
            // Убрали корень, так как мы тут не за вычисление длинны, а нам важен сам факт больше или меньше
            $value = pow($face[1] - $currentFace[1], 2)
                + pow($face[2] - $currentFace[2], 2)
                + pow($face[3] - $currentFace[3], 2);

            $this->_nearest[$i][$index] = $value;
            asort($this->_nearest[$i]);
        }
    }

    /**
     * Создание информации о близости к конкретному лицу
     * @param int $index Индекс в массиве
     */
    private function _createNearest(int $index)
    {
        $currentFace = $this->_data[$index];
        $nearest = [];
        foreach ($this->_data as $i => $face) {
            // Пропускаем сами себя
            if ($i === $index) {
                continue;
            }
            // Убрали корень, так как мы тут не за вычисление длинны, а нам важен сам факт больше или меньше
            $value = pow($face[1] - $currentFace[1], 2)
                + pow($face[2] - $currentFace[2], 2)
                + pow($face[3] - $currentFace[3], 2);
            $nearest[$i] = $value;
        }
        // Сортируем по близости
        asort($nearest);
        $this->_nearest[$index] = $nearest;
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
        // тут вся затея рушится как карточный домик...
        if ($exceptionId >= 0 && isset($this->_nearest[$exceptionId - 1])) {
            $data = array_keys(array_slice($this->_nearest[$exceptionId - 1], 0, $count, true));
        } else {
            $nearest = [];
            foreach ($this->_data as $i => $face) {
                // Убрали корень, так как мы тут не за вычисление длинны, а нам важен сам факт больше или меньше
                $nearest[$i] = pow($face[1] - $race, 2)
                    + pow($face[2] - $emotion, 2)
                    + pow($face[3] - $oldness, 2);
            }
            asort($nearest);
            $data = array_keys(array_slice($nearest, 0, $count, true));
        }

        $result = [];
        if (isset($data)) {
            foreach ($data as $index) {
                $row = $this->_data[$index];
                $result[] = new Face($row[1], $row[2], $row[3], $row[0]);
            }
        }

        return $result;
    }

    /**
     * Расширяем функцию очистки, чтобы освободить память
     */
    public function flush(): void
    {
        parent::flush();

        $this->_data = [];
        $this->_nearest = [];
        $this->_count = 0;
    }
}
