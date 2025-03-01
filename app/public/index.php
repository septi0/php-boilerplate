<?php

// load base app
$di = require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../web_core/App.php';

use WebCore\App;

$app_path = __DIR__ . '/..';

$app = new App($di, $app_path);

$app->run();
