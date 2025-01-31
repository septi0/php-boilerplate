<?php

return [
    'app_env' => getenv('APP_ENV') ?: 'development',
    'app_debug' => getenv('APP_DEBUG') ?: true,

    // Session name
    'sess_name' => 'SESSID',
];