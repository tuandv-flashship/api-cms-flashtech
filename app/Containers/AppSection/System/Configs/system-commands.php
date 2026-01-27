<?php

return [
    'enabled' => env('SYSTEM_COMMANDS_ENABLED', env('APP_ENV') !== 'production'),
    'throttle' => env('SYSTEM_COMMANDS_THROTTLE', '3,1'),
    'result_ttl' => env('SYSTEM_COMMANDS_RESULT_TTL', 600),
    'commands' => [
        'cache_clear' => [
            'command' => 'cache:clear',
            'options' => [],
        ],
        'config_cache' => [
            'command' => 'config:cache',
            'options' => [],
        ],
        'migrate' => [
            'command' => 'migrate',
            'options' => [
                '--force' => true,
                '--no-interaction' => true,
            ],
        ],
        'storage_link' => [
            'command' => 'storage:link',
            'options' => [],
        ],
        'queue_restart' => [
            'command' => 'queue:restart',
            'options' => [],
        ],
        'lang_publish' => [
            'command' => 'lang:publish',
            'options' => [],
        ],
        'permissions_sync' => [
            'command' => 'apiato:permissions-sync',
            'options' => [
                '--prune' => true,
            ],
        ],
    ],
];
