<?php

return [
    'environment' => getenv('APP_ENV') ?: 'development',
    'debug' => getenv('APP_DEBUG') ?: true,

    'base_url' => '/',

    // Session name
    'sess_name' => 'SESSID',
];
