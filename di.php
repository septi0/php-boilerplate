<?php

$di->set('Database', function ($container) {
    $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME');
    return new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
}, true);