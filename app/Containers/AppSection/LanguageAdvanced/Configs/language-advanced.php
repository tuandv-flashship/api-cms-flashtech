<?php

return [
    'supported' => [
        \App\Containers\AppSection\Blog\Models\Post::class => ['name', 'description', 'content'],
        \App\Containers\AppSection\Blog\Models\Category::class => ['name', 'description'],
        \App\Containers\AppSection\Blog\Models\Tag::class => ['name', 'description'],
    ],
    'translatable_meta_boxes' => [
        'seo_meta',
    ],
];
