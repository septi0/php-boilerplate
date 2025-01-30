<?php

// define environment
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

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

// libraries spl autoloader
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    require_once __DIR__ . '/libraries/' . $class . '.php';
});