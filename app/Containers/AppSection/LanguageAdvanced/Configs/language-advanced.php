<?php

return [
    'supported' => [
        \App\Containers\AppSection\Blog\Models\Post::class => ['name', 'description', 'content'],
        \App\Containers\AppSection\Blog\Models\Category::class => ['name', 'description'],
        \App\Containers\AppSection\Blog\Models\Tag::class => ['name', 'description'],
        \App\Containers\AppSection\Gallery\Models\Gallery::class => ['name', 'description'],
        \App\Containers\AppSection\Gallery\Models\GalleryMeta::class => ['images'],
        \App\Containers\AppSection\Page\Models\Page::class => ['name', 'description', 'content'],
        \App\Containers\AppSection\CustomField\Models\CustomField::class => ['value'],
    ],
    'translatable_meta_boxes' => [
        'seo_meta',
    ],
];
