<?php

// load .env file
if (file_exists(__DIR__ . '/.env')) {
    $env = file_get_contents(__DIR__ . '/.env');
    $env = explode("\n", $env);

    foreach ($env as $line) {
        if (strpos($line, '=') !== false) {
            putenv($line);
        }
    }
}

// define environment
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');

// set error reporting
if (defined('ENVIRONMENT')) {
    switch (ENVIRONMENT) {
        case 'development':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            break;
        case 'testing':
        case 'production':
            error_reporting(0);
            ini_set('display_errors', 0);
            break;
        default:
            exit('The application environment is not set correctly.');
    }
}

date_default_timezone_set('Europe/Bucharest');

// autoload classes from libraries and services
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/libraries/',
        __DIR__ . '/services/',
    ];

    $class = str_replace('\\', '/', $class);

    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

$di = new DI();

require_once __DIR__ . '/di.php';

return $di;