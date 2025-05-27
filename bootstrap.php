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
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// set error reporting
if (defined('ENVIRONMENT')) {
    switch (ENVIRONMENT) {
        case 'development':
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            break;
        case 'testing':
        case 'production':
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            break;
        default:
            exit('The application environment is not set correctly.');
    }
}

if (getenv('TZ')) {
    date_default_timezone_set(getenv('TZ'));
}

$autoload_dirs = [
    '\\' => [
        __DIR__ . '/libraries/',
        __DIR__ . '/services/',
    __DIR__ . '/repositories/',
    ],
    'WebCore' => [
        __DIR__ . '/web_core/',
    ],
    ];

$autoload_class_definitions = [
//    'PHPMailer' => [
//        __DIR__ . '/libraries/phpmailer/src/PHPMailer.php',
//        __DIR__ . '/libraries/phpmailer/src/SMTP.php',
//        __DIR__ . '/libraries/phpmailer/src/Exception.php',
//    ],
];

// autoload register classes
spl_autoload_register(function ($namespaced_class) use ($autoload_dirs, $autoload_class_definitions) {
    $namespaced_class = ltrim($namespaced_class, '\\');
    $parts = explode('\\', $namespaced_class);

    $class = array_pop($parts);
    $namespace = implode('\\', $parts) ?: '\\';

    if (isset($autoload_class_definitions[$class])) {
        foreach ($autoload_class_definitions[$class] as $file) require $file;
        return;
    }

    foreach ($autoload_dirs[$namespace] as $directory) {
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
