<?php

declare(strict_types=1);

spl_autoload_register(function ($className) {
    // is valid class name
    if (!preg_match('/^([a-zA-Z0-9\\\]+)$/', $className)) {
        return;
    }

    $className[0] = strtolower($className[0]);

    // remove slash in name class
    $className = str_replace('/', '', $className);

    // \ in namespace to folder
    $className = str_replace('\\', '/', $className);

    // load file
    require_once __DIR__ . '/../' . $className . '.php';
});