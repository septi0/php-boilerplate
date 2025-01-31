<?php

$app_path = __DIR__ . '/../app/';

require $app_path . '/autoload.php';

$core = [
    'App',
    'Route',
    'Router',
    'Message',
    'Request',
    'Response',
    'ResponseEmitter',
    'Template',
    'Session',
];

foreach ($core as $class) {
    require_once __DIR__ . '/libraries/' . $class . '.php';
}

foreach ($middlewares as $middleware) {
    require_once $app_path . '/middlewares/' . $middleware . '.php';
}

$app = new App($app_path, $di);

$app->run();