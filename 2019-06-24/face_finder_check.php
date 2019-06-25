<?php

// Строгая типизация
declare(strict_types=1);

// Подключаем наши классы
require_once 'face_finder_in_memory.php';
require_once 'face_finder_in_database.php';
require_once 'face_finder_in_cluster.php';

/**
 * Если честно очень не понятно что имелось ввиду под фразой:
 *
 * Также стоит учитывать, что на 1 экземпляр класса может приходиться неограниченное
 * количество операций поиска и добавления лиц (например, если компонент будет
 * использоваться в демонизированом процессе).
 *
 * Толи то что я могу хранить данные в памяти и работать с ними экономя на запросах
 * и использовать какие-то из алгоритмов поиска изменив структуру данных под себя,
 * толи то что может быть одновременно несколько запросов быть, так как неограниченное
 * количество операций поиска звучит очень странно. Сделал вариант работы с памятью
 * с простым перебором, и сделал на основе запросов, чтобы можно было это как-то запускать параллельно.
 *
 * Из-за ограничение в использованиях библиотек, я опустил момент с подбором более адекватного решения,
 * так как некоторые алгоритмы появились "относительно" недавно. Я как-то больше склоняюсь к
 * использованию уже созданных алгоритмов, но только для вас написал велосипеды =)
 *
 * FaceFinderInMemory - С использование памяти для хранения данных и кэшированием результата
 * FaceFinderInDataBase - С использование базы данных, при // запуске,
 *      если не настраивать синхронизацию между классами
 * FaceFinderInCluster - Ну тут я уже пошёл разнос, применил один из алгоритмов для оптимизации
 */
$classes = [
    //'FaceFinderInMemory', // Решение долго загружаемое, и решение в лоб (перебор) + сохранение результата в некий кэш
    //'FaceFinderInDataBase', // Решение достаточно быстро работающее, но оно тоже в лоб, но с помощью БД, без кэша
    'FaceFinderInCluster', // Решение описано в файле.
];

/**
 * Функция для запуска классов по работе с данными
 * В условиях задачи её не было, но я тестировал алгоритмы и оставил их реализации,
 * так как не до конца понял некоторые ограничения
 *
 * @param string $faceFinderClass Используемые класс
 */
function testFaceFinderClass(string $faceFinderClass): void
{
    $ff = new $faceFinderClass();
    if (!($ff instanceof FaceFinderInterface)) {
        return;
    }

    $ff->flush();
    # add and search first face
    $faces = $ff->resolve(new Face(1, 200, 500));
    assert(count($faces) === 1 && $faces[0]->getId() === 1);

    # add +1 face
    $faces = $ff->resolve(new Face(55, 100, 999));
    assert(count($faces) === 2 && $faces[0]->getId() === 2);

    # only search, not adding (because id != 0)
    $faces = $ff->resolve(new Face(55, 100, 999, 2));
    assert(count($faces) === 2 && $faces[0]->getId() === 2);

    // Не уверен что функция поиска и добавления одновременно хорошее решение,
    // так как по сути мы тут вообще никак не обрабатываем ситуацию с поиском
    # add 1000 random faces
    for ($a = 0; $a < 1000; $a++) {
        $ff->resolve(
            new Face(
                rand(0, 100),
                rand(0, 1000),
                rand(0, 1000)
            )
        );
    }

    # let's recreate instance
    unset($ff);
    $ff = new $faceFinderClass();
    if (!($ff instanceof FaceFinderInterface)) {
        return;
    }

    /**
     * Тут у вас код и комментарии отличает в PDF и на Git, взял то что показалось чуть логичнее,
     * так как не описано поведение программы при передачи отличных данных с
     * существующим ID
     *
     * В PDF:
     * $faces = $ff->resolve(new Face(54, 101, 998, 99999));
     *
     * На Git (...URL...):
     * $faces = $ff->resolve(new Face(54, 101, 998, 1));
     */
    # find known similar face and check first 3 records to match
    # Record with id=99999 not exists, this id is necessary to prevent
    # adding new face into DB
    $faces = $ff->resolve(new Face(54, 101, 998, 99999));
    assert(
        count($faces) === 5
        && (
            $faces[0]->getId() === 2
            || $faces[1]->getId() === 2
            || $faces[3]->getId() === 2
        )
    );

    $ff->flush();
}

try {
    // Махалай запускай всё подряд...
    array_walk($classes, 'testFaceFinderClass');
} catch (Exception $e) {
    /**
     * В условиях задачи этот момент никак не отражен...
     */
    // error_log($e->getMessage());
    // error_log($$e->getTraceAsString());
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

