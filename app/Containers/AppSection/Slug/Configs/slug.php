<?php

return [
    'general' => [
        'pattern' => '--slug--',
        'supported' => [
            \App\Containers\AppSection\Blog\Models\Post::class => \App\Containers\AppSection\Blog\Models\Post::class,
            \App\Containers\AppSection\Blog\Models\Category::class => \App\Containers\AppSection\Blog\Models\Category::class,
            \App\Containers\AppSection\Blog\Models\Tag::class => \App\Containers\AppSection\Blog\Models\Tag::class,
            \App\Containers\AppSection\Gallery\Models\Gallery::class => \App\Containers\AppSection\Gallery\Models\Gallery::class,
            \App\Containers\AppSection\Page\Models\Page::class => \App\Containers\AppSection\Page\Models\Page::class,
        ],
        'prefixes' => [
            \App\Containers\AppSection\Blog\Models\Post::class => '',
            \App\Containers\AppSection\Blog\Models\Category::class => '',
            \App\Containers\AppSection\Blog\Models\Tag::class => 'tag',
            \App\Containers\AppSection\Gallery\Models\Gallery::class => 'galleries',
            \App\Containers\AppSection\Page\Models\Page::class => '',
        ],
        'disable_preview' => [],
        'slug_generated_columns' => [
            \App\Containers\AppSection\Blog\Models\Post::class => 'name',
            \App\Containers\AppSection\Blog\Models\Category::class => 'name',
            \App\Containers\AppSection\Blog\Models\Tag::class => 'name',
            \App\Containers\AppSection\Gallery\Models\Gallery::class => 'name',
            \App\Containers\AppSection\Page\Models\Page::class => 'name',
        ],
        'enable_slug_translator' => env('CMS_ENABLE_SLUG_TRANSLATOR', false),
        'public_single_ending_url' => env('SLUG_PUBLIC_SINGLE_ENDING_URL', ''),
    ],
];
