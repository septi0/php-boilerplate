<?php

return [
    'environment' => getenv('APP_ENV') ?: 'development',
    'debug' => getenv('APP_DEBUG') ?: true,

    // Session name
    'sess_name' => 'SESSID',
];