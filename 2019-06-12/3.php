<?php

/**
 * @charset UTF-8
 *
 * Работа с массивами и строками.
 *
 * Есть список адресов домов.
 *
 * Необходимо написать функцию, которая формирует множество возможных написаний адреса
 * с учетом возможных сокращений, перестановок, записей номера дома большой/маленькой буквой,
 * записей корпуса через дробь/дефис.
 *
 * Пример:
 *
 * Есть адрес, заданный массивом:
 *
 * array(
 *       "Ленина ул.",
 *       "14а"
 * )
 *
 * Функция должна вернуть массив:
 *
 * array(
 *    'Ленина ул. 14а',
 *      'Ленина ул. 14А',
 *      'Ленина улица 14а',
 *      'Ленина улица 14А',
 *      'ул. Ленина 14а',
 *      'ул. Ленина 14А',
 *      'улица Ленина 14-а',
 *      'улица Ленина 14-А',
 *      'Ленина ул. 14-а',
 *      'Ленина ул. 14-А',
 *      'Ленина улица 14-а',
 *      'Ленина улица 14-А',
 *      'ул. Ленина 14-а',
 *      'ул. Ленина 14-А',
 *      'улица Ленина 14-а',
 *      'улица Ленина 14-А',
 *      'Ленина ул. 14/а',
 *      'Ленина ул. 14/А',
 *      'Ленина улица 14/а',
 *      'Ленина улица 14/А',
 *      'ул. Ленина 14/а',
 *      'ул. Ленина 14/А',
 *      'улица Ленина 14/а',
 *      'улица Ленина 14/А'
 * )
 */

# Можно использовать список:

$addresses = array(
    array(
        "Ленина ул.",
        "14а"
    ),
    array(
        "Таёжный пер.",
        "17-2"
    ),
    array(
        "Горняков просп.",
        "13Б"
    ),
);

// массив сокращенных названий
$shortTypes = [
    'улица' => 'ул.',
    'переулок' => 'пер.',
    'проспект' => 'просп.',
];

// форматирование адреса по заданым правилам
function getFormatAddress(
    $streetName,
    $streetType,
    $houseNumber,
    $houseSubNumber = null,
    $isShortStreetType = false,
    $isLeftRightStreet = false,
    $delimiterHouse = '/',
    $isHouseSubNumberUpper = false
): string
{
    $string = '';

    if ($isShortStreetType) {
        global $shortTypes;
        foreach ($shortTypes as $key => $value) {
            if ($key === $streetType) {
                $streetType = $value;
                break;
            }
        }
    }

    if ($isLeftRightStreet) {
        $string .= $streetType . ' ' . $streetName;
    } else {
        $string .= $streetName . ' ' . $streetType;
    }

    $string .= ' ' . $houseNumber;
    if (null !== $houseSubNumber) {
        $string .= $delimiterHouse;
        if ($isHouseSubNumberUpper) {
            $string .= mb_strtoupper($houseSubNumber);
        } else {
            $string .= mb_strtolower($houseSubNumber);
        }
    }

    return $string;
}

// виды разделение корпуса от дома
$houseDelimiters = [
    '',
    '-',
    '/',
];

// расшифровка корпуса и номера дома
function getNumberAndSubNumber(string $houseNumber): array
{
    $length = mb_strlen($houseNumber);
    $number = '';
    $subNumber = '';
    $subNumberStarted = false;
    global $houseDelimiters;
    for ($i = 0; $i < $length; $i++) {
        $symbol = mb_substr($houseNumber, $i, 1);

        if (!$subNumberStarted && !is_numeric($symbol)) {
            $subNumberStarted = true;
        }

        if (in_array($symbol, $houseDelimiters)) {
            continue;
        }

        if ($subNumberStarted) {
            $subNumber .= $symbol;
        } else {
            $number .= $symbol;
        }
    }

    return [$number, empty($subNumber) ? null : $subNumber];
}

// мультибайтовая замена строки, взята с интернета)
function mb_str_replace($needle, $replace_text, $haystack) {
    return implode($replace_text, mb_split($needle, $haystack));
}

// генерация адресов из примера
function generateAddresses(string $street, string $house): array
{
    // можно ли делать корпус с большой или малой буквы, так как есть корпуса с цифрами
    $canUpperAndLowerSubNumber = true;

    $houseNumbers = getNumberAndSubNumber($house);
    $houseNumber = $houseNumbers[0];
    $houseSubNumber = $houseNumbers[1];

    // цифры и адреса без корпусов не поддаются большой и малой букве
    if (is_numeric($houseSubNumber) || null === $houseSubNumber) {
        $canUpperAndLowerSubNumber = false;
    }

    $streetName = null;
    $streetType = null;

    // определяем полное название типа улица или проспект, можно потом сделать кучу разных комбинаций из возможных сокращений
    global $shortTypes;
    foreach ($shortTypes as $longType => $shortType) {
        if (mb_strpos($street, $shortType) !== false || mb_strpos($street, $longType) !== false) {
            $streetType = $longType;
            $streetName = mb_str_replace($shortType, '', $street);
            $streetName = mb_str_replace($longType, '', $streetName);
            $streetName = trim($streetName);
            break;
        }
    }

    // базовый набор правил форматирования для функции, поидее можно было бы это сделать опциями, чтобы на индексы не ссылаться
    $combinationCalls = [];

    $combinationCalls[] = [$streetName, $streetType, $houseNumber, $houseSubNumber, false, false, '/', false];
    $combinationCalls[] = [$streetName, $streetType, $houseNumber, $houseSubNumber, false, true, '/', false];
    $combinationCalls[] = [$streetName, $streetType, $houseNumber, $houseSubNumber, true, false, '/', false];
    $combinationCalls[] = [$streetName, $streetType, $houseNumber, $houseSubNumber, true, true, '/', false];

    // если есть корпус добавляем ещё разные форматы
    if (!empty($houseSubNumber)) {
        global $houseDelimiters;
        $newCalls = [];
        foreach ($houseDelimiters as $houseDelimiter) {
            // exists call
            if ($houseDelimiter === '/') {
                continue;
            }
            // skip number . number
            if (is_numeric($houseSubNumber) && $houseDelimiter === '') {
                continue;
            }
            foreach ($combinationCalls as $call) {
                // 6-ой параметр в функции отвечает за разделение корпуса от номера дома
                $call[6] = $houseDelimiter;
                $newCalls[] = $call;
            }
        }
        $combinationCalls = array_merge($combinationCalls, $newCalls);

        if ($canUpperAndLowerSubNumber) {
            $newCalls = [];
            foreach ($combinationCalls as $call) {
                // 7ой параметр в функции отпечает за большую и малую букву
                $call[7] = true;
                $newCalls[] = $call;
            }
            $combinationCalls = array_merge($combinationCalls, $newCalls);
        }
    }

    // генерируем поток данных))
    $result = [];
    foreach ($combinationCalls as $call) {
        $result[] = call_user_func_array('getFormatAddress', $call);
    }

    return $result;
}

// смотрим...
foreach ($addresses as $address) {
    echo "{$address[0]} {$address[1]}: list =><br>" . PHP_EOL;
    $results = generateAddresses($address[0], $address[1]);
    foreach ($results as $row) {
        echo "    {$row}<br>" . PHP_EOL;
    }

    echo "<br>" . PHP_EOL;
}