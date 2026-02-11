<?php

return [
    'reference_types' => [
        'page' => \App\Containers\AppSection\Page\Models\Page::class,
        'category' => \App\Containers\AppSection\Blog\Models\Category::class,
        'post' => \App\Containers\AppSection\Blog\Models\Post::class,
        'tag' => \App\Containers\AppSection\Blog\Models\Tag::class,
    ],
    'locations' => [
        'main-menu' => 'Main Navigation',
        'footer' => 'Footer Menu',
    ],
    'cache' => [
        'ttl_seconds' => env('MENU_CACHE_TTL_SECONDS', 86400),
    ],
];
