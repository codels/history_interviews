<?php

/**
 * @charset UTF-8
 *
 * Задание 2. Работа с массивами и строками.
 *
 * Есть список временных интервалов (интервалы записаны в формате чч:мм-чч:мм).
 *
 * Необходимо написать две функции:
 *
 *
 * Первая функция должна проверять временной интервал на валидность
 *    принимать она будет один параметр: временной интервал (строка в формате чч:мм-чч:мм)
 *    возвращать boolean
 *
 *
 * Вторая функция должна проверять "наложение интервалов" при попытке добавить новый интервал в список существующих
 *    принимать она будет один параметр: временной интервал (строка в формате чч:мм-чч:мм)
 *  возвращать boolean
 *
 *  "наложение интервалов" - это когда в промежутке между началом и окончанием одного интервала,
 *   встречается начало, окончание или то и другое одновременно, другого интервала
 *
 *  пример:
 *
 *  есть интервалы
 *    "10:00-14:00"
 *    "16:00-20:00"
 *
 *  пытаемся добавить еще один интервал
 *    "09:00-11:00" => произошло наложение
 *    "11:00-13:00" => произошло наложение
 *    "14:00-16:00" => наложения нет
 *    "14:00-17:00" => произошло наложение
 */

# Можно использовать список:

$list = array(
    '09:00-11:00',
    '11:00-13:00',
    '15:00-16:00',
    '17:00-20:00',
    '20:30-21:30',
    '21:30-22:30',
);

// Проверка валидности временного интервала
function isValidTimeInterval(string $timeInterval): bool
{
    $matches = [];
    // проверяем формат данных
    if (!preg_match('/^([0-9]{2}):([0-9]{2})-([0-9]{2}):([0-9]{2})$/', $timeInterval, $matches)) {
        return false;
    }
    // проверяем сами данные
    // 0 - сама строка
    // 1 - от часов
    // 2 - от минут
    // 3 - до часов
    // 4 - до минут

    // не корректные часы от
    if ($matches[1] > 23 || $matches[1] < 0) {
        return false;
    }

    // не корректные минуты от
    if ($matches[2] > 60 || $matches[2] < 0) {
        return false;
    }

    // не корректные часы до
    if ($matches[3] > 23 || $matches[3] < 0) {
        return false;
    }

    // не корректные минуты до
    if ($matches[4] > 60 || $matches[4] < 0) {
        return false;
    }

    $totalMinutesFrom = (($matches[1] * 60) + $matches[2]);
    $totalMinutesTo = (($matches[3] * 60) + $matches[4]);

    // Время начала, больше времени конца
    if ($totalMinutesFrom > $totalMinutesTo) {
        return false;
    }

    return true;
}

// Перевод времени в минуты, чтобы удобнее было сравнивать
function timeToMinutes(string $time): int
{
    $times = explode(':', $time);
    return ($times[0] * 60) + $times[1];
}

// Преобразовать интервел в мин и макс время
function intervalToMinMax(string $timeInterval): array
{
    $times = explode('-', $timeInterval);
    return [
        timeToMinutes($times[0]),
        timeToMinutes($times[1]),
    ];
}

// Проверка наложения временных интервалов, ВОЗВРАЩАЕТ TRUE ЕСЛИ ЕСТЬ НАЛОЖЕНИЕ!!!! так как ищет коллизию!!!! а не свободное место, можно поменять... условий нет
function checkTimeIntervalCollision(string $timeInterval): bool
{
    // Так как не сказано что в функцию надо передавать список, то получаю через глобальную переменную
    global $list;

    $minMaxMinutes = intervalToMinMax($timeInterval);

    foreach ($list as $rowTimeInterval) {
        $mixMaxRowInterval = intervalToMinMax($rowTimeInterval);
        // пропускаем интервалы которые заканчиваются раньше, чем начинается поступивший интервал
        if ($mixMaxRowInterval[1] <= $minMaxMinutes[0]) {
            continue;
        }

        // пропускаем интервалы которые начинаются позже, чем заканчивается поступивший интервал
        if ($mixMaxRowInterval[0] >= $minMaxMinutes[1]) {
            continue;
        }

        return true;
    }

    return false;
}

$validListInterval = [
    'dasd1',
    'dsa90-2fd',
    'ds:w2-a1:90',
    '34:10-10:10',
    '12:14-15:16',
    '15:00-12:00',
];

foreach ($validListInterval as $value) {
    echo "{$value} => ". isValidTimeInterval($value) . "<br>".PHP_EOL;
}

echo "<br><br>".PHP_EOL.PHP_EOL;

// Проверка примера
$tempList = $list; // копируем значения
// заменяем массив
$list = [
    '10:00-14:00',
    '16:00-20:00'
];

// список проверяемых интервалов
$checkList = [
    '09:00-11:00',
    '11:00-13:00',
    '14:00-16:00',
    '14:00-17:00'
];

foreach ($checkList as $check) {
    echo "{$check} => ".checkTimeIntervalCollision($check)."<br>".PHP_EOL;
}

echo "<br><br>".PHP_EOL.PHP_EOL;

// новый проверяемых интервалов
$checkList = $tempList;

foreach ($checkList as $check) {
    echo "{$check} => ".checkTimeIntervalCollision($check)."<br>".PHP_EOL;
}


echo "<br><br>".PHP_EOL.PHP_EOL;

// ещё немного тестов...
$checkList = [
    '09:00-09:30',
    '09:00-10:00',
    '09:00-11:00',
    '09:00-15:00',
    '09:00-17:00',
    '09:00-21:00',
    '11:00-13:00',
    '11:00-15:00',
    '11:00-17:00',
    '11:00-21:00',
    '15:00-16:00',
    '15:00-17:00',
    '15:00-21:00',
    '16:00-18:00',
    '16:00-20:00',
    '16:00-21:00',
    '20:00-21:00',
    '21:00-23:00',
];

foreach ($checkList as $check) {
    echo "{$check} => ".checkTimeIntervalCollision($check)."<br>".PHP_EOL;
}