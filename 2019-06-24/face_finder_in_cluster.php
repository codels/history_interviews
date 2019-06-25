<?php

// Строгая типизация
declare(strict_types=1);

// Используемый базовый класс
require_once 'face_finder_base_classes.php';

/**
 * При подходе к решению данной проблемы мы переведем задачу в более
 * математически удобные условия:
 * 1. лицо - это точка в пространстве
 * 2. точка в просранстве имеет 3 координаты (x, y, z), которыми являются свойства лица
 *
 * Теперь мы можем думать над использованием алгоритмов по поиску ближайшей точки или соседей,
 * для решения данной проблемы есть несколько структур данных и алгоритмов, чаще
 * они уже реализованы библиотеками, и я крайне рекомендую использовать библиотеки,
 * а не создавать велосипеды.
 *
 * В данном классе мы будем наше трёх мерное пространство делить на кластеры.
 * Размер кластера влияет как на скорость инициализации, так и на точность
 * выборки. Это решение не является 100% точным, оно является приближенным поиском,
 * которое может удовлетворить поставленные задачи (assert'ы).
 * Кластер из себя представляет куб в пространстве, для более точного поиска,
 * мы будем искать N точек в кубе и рядом с ним в кубах (рядом будем вычислено по формуле
 * растояние вектора).
 *
 * Так как поиск точек отдаем базе данных, то мы можем запускать запросы //, будут свои не точности
 * которые возможно стоит уточнить и реализовать (синхронизация размеров кластеров)
 *
 * Возможно я сделаю ошибки в логике, но думаю хотя бы опишу то что пытался сделать уже под ночь =)
 * Я бы хотел возможно расширить тестирование данного метода, и увеличить количество данных
 * возмонжо на сотнях тысячах и миллионах алгоритмы лучше выбирать чем на 10000, если мы конечно
 * рассматриваем большие задачи.
 *
 * Class FaceFinderInCluster
 *
 * @property int $_clusterInX Размер пространства для расы
 * @property int $_clusterInY Размер пространства для эмоции
 * @property int $_clusterInZ Размер пространства для старости
 * @property int[] $_clusterSizes Количество точек в кластере, необходимо для формирования
 *      количества точек для сравнения
 * @property array[] $_clusterNearest Близость других кубов относительно куба с ключем значения
 */
class FaceFinderInCluster extends FaceFinder
{
    // Размер куба в пространстве, чем меньше куб, тем точнее поиск
    const CLUSTER_SIZE = 100;

    // Минимальное количество найденных точек для сравнения, чем больше строк, тем точнее
    const COMPARE_ROWS_MIN = 100;

    // Максимальные размеры пространства
    const MAX_X = 100;
    const MAX_Y = 1000;
    const MAX_Z = 1000;

    // Расчитываем максимальное количество кубов
    // Тут можно сделать лучше, но временным решением на 10 часов пока подойдёт
    // todo Рефакторинг
    private $_clusterInX = 0;
    private $_clusterInY = 0;
    private $_clusterInZ = 0;

    private $_clusterSizes = [];
    private $_clusterNearest = [];

    /**
     * Данный конструктор может начать медленно работать если мы будем делить пространство на
     * очень маленькие кубы, которые могут повысить точность и СКОРОСТЬ работы при огромном объёме данных
     *
     * FaceFinderInCluster constructor.
     */
    public function __construct()
    {
        // Делаем грязные дела от базового контруктора
        // Лучше бы конечно было предусмотреть какой-то вариант DI
        parent::__construct();

        // Определяем количество кубов по разным осям
        $this->_clusterInX = self::MAX_X / self::CLUSTER_SIZE;
        $this->_clusterInY = self::MAX_Y / self::CLUSTER_SIZE;
        $this->_clusterInZ = self::MAX_Z / self::CLUSTER_SIZE;

        /**
         * Подсчитываем количество кубов в пространстве
         * Обнулям им размер и формируем расстояние от каждого куба к другому кубу
         */
        $clustersCount = $this->_clusterInX * $this->_clusterInY * $this->_clusterInZ;
        for ($i = 0; $i < $clustersCount; $i++) {
            // Сброс количества точек
            $this->_clusterSizes[$i] = 0;

            // "близость" к другим кубам
            $nearest = [];
            // Точка в пространстве для кластера с данным индексом
            $currentPoint = $this->getPointFromClusterIndex($i);

            // Перебор кластеров
            for ($j = 0; $j < $clustersCount; $j++) {
                // Не генерируем расстояние для самого себя
                if ($i === $j) {
                    continue;
                }
                // Получаем координаты в пространстве для кластера с искомым индексом
                $pointInOtherCluster = $this->getPointFromClusterIndex($j);
                // Вносим информацию о близости по ключу индекса искомого кластера
                $nearest[$j] = pow($currentPoint[0], $pointInOtherCluster[0])
                    + pow($currentPoint[1], $pointInOtherCluster[1])
                    + pow($currentPoint[2], $pointInOtherCluster[2]);
            }
            // Сортируем информацию о близости
            asort($nearest);
            // Сохраняем в наш массив для быстрого поиска ближ кубов при формировании кол-ва точек
            $this->_clusterNearest[$i] = $nearest;
        }

        // Получаем количество точек в кластере находящихся уже в базе
        // Если мы будем запускать несколько демонов //, то нужно настроить перодическую синхронизацию
        // чтобы не пропускать необходимые кластеры, так же сюда возмонжо стоит добавить ограничение на 10000.
        $statement = DataBase::pdo()->prepare('
            SELECT  `cluster_index`,
                    COUNT(*) AS `size`
            FROM `' . self::TABLE_NAME . '`
            GROUP BY `cluster_index`
        ');
        $statement->execute();
        $result = $statement->fetchAll();
        // Сохраняем полученную информацию в нашем массиве
        foreach ($result as $row) {
            $this->_clusterSizes[$row['cluster_index']] = $row['size'];
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
        $clusterIndex = $this->getClusterIndex($face->getRace(), $face->getEmotion(), $face->getOldness());
        // Создаем новую запись если нет ID
        if ($face->getId() === 0) {
            $id = $this->addFace($face, $clusterIndex);
            $this->_clusterSizes[$clusterIndex]++;
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
            (isset($newFace) ? $newFace->getId() : 0),
            $clusterIndex
        );

        // Если есть новое лицо, добавляем его первым, как самое подходящее
        if (isset($newFace)) {
            array_unshift($faces, $newFace);
        }

        return $faces;
    }

    /**
     * Получить координаты кластера по его индексу
     * Тут я больше всего боюсь напортачить вечером =(
     *
     * @param int $index Индекс кластера
     * @return array Координаты в формете [x, y, z]
     */
    private function getPointFromClusterIndex(int $index): array
    {
        return [
            ($index % ($this->_clusterInX)),// x
            ($index / ($this->_clusterInX)),// y,
            ($index / ($this->_clusterInX * $this->_clusterInY)),// z,
        ];
    }

    /**
     * Получить индекс кластера по координатам
     * @param int $x
     * @param int $y
     * @param int $z
     * @return int $index Индекс кластера
     */
    private function getClusterIndex(int $x, int $y, int $z): int
    {
        $xIndex = (int)floor($x / self::CLUSTER_SIZE);
        $yIndex = (int)floor($y / self::CLUSTER_SIZE);
        $zIndex = (int)floor($z / self::CLUSTER_SIZE);

        // ограничение чтобы 1000, 1000 и 100 координаты попадали в последний блок,
        // так как у нас координаты с 0, то ровно не делятся =( было бы удобнее ограничение в 999
        if ($zIndex >= $this->_clusterInZ) {
            $zIndex = $this->_clusterInZ - 1;
        }
        if ($yIndex >= $this->_clusterInY) {
            $yIndex = $this->_clusterInY - 1;
        }
        if ($xIndex >= $this->_clusterInX) {
            $xIndex = $this->_clusterInX - 1;
        }
        return $xIndex + ($yIndex * $this->_clusterInX) + ($zIndex * $this->_clusterInX * $this->_clusterInY);
    }

    /**
     * Функция поиска
     * @param int $race Раса
     * @param int $emotion Эмоция
     * @param int $oldness Старость
     * @param int $count Количество искомых лиц
     * @param int $exceptionId Исключить из поиска номер
     * @param int $clusterIndex Кластер в котором лежит точка, передаем чтобы не тратить вычисления)))
     * @return Face[]
     */
    public function search(int $race,int $emotion, int $oldness, int $count = 5,
                           int $exceptionId = 0, int $clusterIndex = 0): array
    {
        // Получаем ID, чтобы ограничить поиск среди последних 10000
        $maxId = $this->getMaxFaceId();

        // Количество точек в нашем ластере как минимум уже попадет в поиск
        $countPointsForScan = $this->_clusterSizes[$clusterIndex];
        // счетчик перебора ближайших кластеров
        $i = 0;
        // кластеры которые будем сканировать в базе данных
        $clusterIdForScan = [(int)$clusterIndex]; // На страже SQL

        // перебор ближайших отсортированных кластеров
        foreach ($this->_clusterNearest[$clusterIndex] as $clusterNearIndex => $nearL) {
            // Прошли один кластер
            $i++;

            // Если в кластере есть точки то добавляем его для поиска в базе
            if ($this->_clusterSizes[$clusterNearIndex] > 0) {
                $clusterIdForScan[] = (int)$clusterNearIndex; // На страже SQL
                // Увеличиваем количество точек которые мы будем сравнивать
                $countPointsForScan += $this->_clusterSizes[$clusterNearIndex];
            }

            // Если набрали необходимый минимум для сравнения то выходим из массива
            if ($countPointsForScan >= self::COMPARE_ROWS_MIN) {
                break;
            }
        }

        // если все кластеры пустые, ну я пытался...
        if (empty($clusterIdForScan)) {
            return [];
        }

        // формируем номера кластеров в строку, так как у нас строная типизация и
        // индекс кластера это номер то не будем беспокоится о SQL иньекции
        $clusterIdForScanString = implode(',', $clusterIdForScan);

        // Убрали корень, так как мы тут не за вычисление длинны, а нам важен сам факт больше или меньше
        $statement = DataBase::pdo()->prepare('
            SELECT
                    `f`.`id`,
                    `f`.`race`,
                    `f`.`emotion`,
                    `f`.`oldness`,
                    (POW(`f`.`race` - ?, 2) + POW(`f`.`emotion` - ?, 2) + POW(`f`.`oldness` - ?, 2)) AS `l`
            FROM `' . self::TABLE_NAME . '` AS `f`
            WHERE `id` >= ? AND `id` <> ? AND `cluster_index` IN (' . $clusterIdForScanString . ')
            ORDER BY `l` ASC
            LIMIT ' . $count . '
        ');
        // Если не выполнили запрос, отдаем пустоту... мб стоило исключение, никаких условий нет
        if (!$statement->execute([$race, $emotion, $oldness, $maxId - self::SEARCH_IN_LAST_COUNT, $exceptionId])) {
            return [];
        }

        // Формируем в ответ классы Face
        $statement->setFetchMode(\PDO::FETCH_CLASS, 'Face');

        // Отдаем результат
        return $statement->fetchAll();
    }
}
