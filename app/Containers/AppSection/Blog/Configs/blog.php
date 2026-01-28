<?php

return [
    'views' => [
        'enabled' => env('BLOG_VIEWS_ENABLED', true),
        'debounce_seconds' => env('BLOG_VIEWS_DEBOUNCE_SECONDS', 900),
        'cache_prefix' => 'blog:post:view',
    ],
];
