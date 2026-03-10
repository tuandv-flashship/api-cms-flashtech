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
        \App\Containers\AppSection\CustomField\Models\FieldGroup::class => ['title'],
        \App\Containers\AppSection\CustomField\Models\FieldItem::class => ['title', 'instructions', 'options'],
        \App\Containers\AppSection\Menu\Models\MenuNode::class => ['title', 'url'],
        \App\Containers\AppSection\AdminMenu\Models\AdminMenuItem::class => ['name', 'description', 'section'],
    ],
    'translatable_meta_boxes' => [
        'seo_meta',
    ],
];
