<?php

/**
 * @charset UTF-8
 *
 * Задание 1. Работа с массивами.
 *
 * Есть 2 списка: общий список районов и список районов, которые связаны между собой по географии (соседние районы).
 * Есть список сотрудников, которые работают в определённых районах.
 *
 * Необходимо написать функцию для поиска сотрудника, у которого есть полное совпадение по району,
 * если таких сотрудников нет - тогда искать по соседним районам.
 *
 * Функция должна принимать 1 аргумент: название района (строка).
 * Возвращать: логин сотрудника или null.
 *
 */

# Использовать данные:

// Список районов
$areas = array(
    1 => '5-й поселок',
    2 => 'Голиковка',
    3 => 'Древлянка',
    4 => 'Заводская',
    5 => 'Зарека',
    6 => 'Ключевая',
    7 => 'Кукковка',
    8 => 'Новый сайнаволок',
    9 => 'Октябрьский',
    10 => 'Первомайский',
    11 => 'Перевалка',
    12 => 'Сулажгора',
    13 => 'Университетский городок',
    14 => 'Центр',
);

// Близкие районы, связь осуществляется по индентификатору района из массива $areas
$nearby = array(
    1 => array(12, 11),
    2 => array(5, 7, 6, 8),
    3 => array(11, 13),
    4 => array(10, 9, 12),
    5 => array(2, 6, 7, 8),
    6 => array(5, 2, 7, 8),
    7 => array(2, 5, 6, 8),
    8 => array(6, 2, 7, 5),
    9 => array(10, 14),
    10 => array(9, 14, 12),
    11 => array(13, 3, 1, 12),
    12 => array(1, 10),
    13 => array(11, 1, 12),
    14 => array(9, 10),
);

// список сотрудников
$workers = array(
    0 => array(
        'login' => 'login1',
        'area_name' => 'Октябрьский',
    ),
    1 => array(
        'login' => 'login2',
        'area_name' => 'Зарека',
    ),
    2 => array(
        'login' => 'login3',
        'area_name' => 'Сулажгора',
    ),
    3 => array(
        'login' => 'login4',
        'area_name' => 'Древлянка',
    ),
    4 => array(
        'login' => 'login5',
        'area_name' => 'Центр',
    ),
);

// Получение номера района по его имени, для удобства поиска
function getAreaIdByAreaName(string $searchAreaName): ?int
{
    global $areas;
    foreach ($areas as $areaId => $areaName) {
        // сделано пока что строгое сравнение, нет требований в задании
        if ($areaName === $searchAreaName) {
            return (int)$areaId;
        }
    }
    // если не найден номер района, возвращаем null, так как 0 это индекс который существует
    return null;
}

// Создания уровней близости одного района к другим
function createNearLevels(int $areaId, int $level, array &$valuesInArray, array &$nearLevels)
{
    global $nearby;
    $nextSearchAreas = [];
    foreach ($nearby[$areaId] as $nearAreaId) {
        if (in_array($nearAreaId, $valuesInArray)) {
            continue;
        }
        $nearLevels[$level][] = $nearAreaId;
        $valuesInArray[]= $nearAreaId;
        $nextSearchAreas[] = $nearAreaId;
    }

    // возможно стоит предусмотреть максимальную вложенность!
    $nextLevel = ++$level;
    foreach ($nextSearchAreas as $nextSearchAreaId) {
        createNearLevels($nextSearchAreaId, $nextLevel, $valuesInArray, $nearLevels);
    }
}

// Получить логин сотрудника по имени района
function getLoginByAreaName(string $areaName): ?string
{
    // Получаем номер района
    $areaId = getAreaIdByAreaName($areaName);

    // Не найден район в списке
    if (null === $areaId) {
        return null;
    }

    global $workers;

    // 1 уровень, поиск прямой связи и формализация данных
    foreach ($workers as &$worker) {
        // получаем номера районов, не понятно почему данные вообще хранятся в строковом виде
        $worker['area_id'] = getAreaIdByAreaName($worker['area_name']);

        // если есть прямая связь, то возвращаем значение
        if ($areaId === $worker['area_id']) {
            return $worker['login'];
        }
    }
    // очищаем переменную, так как работали с сылкой
    unset($worker);

    // Создаем массив с зонами которые мы уже искали, массив с уровенями "близости" к каждой зоне
    $valuesInArray = [$areaId];
    $nearLevels = [];
    createNearLevels($areaId, 0, $valuesInArray, $nearLevels);

    foreach ($nearLevels as $nearLevel) {
        foreach ($nearLevel as $subAreaId) {
            foreach ($workers as $worker) {
                if ($worker['area_id'] === $subAreaId) {
                    return $worker['login'];
                }
            }
        }
    }

    return null;
}

foreach ($areas as $areaName) {
    echo "$areaName => " . getLoginByAreaName($areaName) . "<br>";
}