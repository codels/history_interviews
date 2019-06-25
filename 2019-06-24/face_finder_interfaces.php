<?php

// Строгая типизация
declare(strict_types=1);

interface FaceInterface {
    /**
     * Returns face id or 0, if face is new
     */
    public function getId(): int;

    /**
     * Returns race parameter: from 0 to 100.
     */
    public function getRace(): int;

    /**
     * Returns face emotion level: from 0 to 1000.
     */
    public function getEmotion(): int;

    /**
     * Returns face oldness level: from 0 to 1000.
     */
    public function getOldness(): int;
}

interface FaceFinderInterface {
    /**
     * Finds 5 most similar faces in DB.
     * If the specified face is new (id=0),
     * then it will be saved to DB.
     *
     * @param FaceInterface $face Face to find and save (if id=0)
     * @return FaceInterface[] List of 5 most similar faces,
     * including the searched one
     */
    public function resolve(FaceInterface $face): array;

    /**
     * Removes all faces in DB and (!) reset faces id sequence
     */
    public function flush(): void;
}
