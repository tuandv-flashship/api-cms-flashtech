<?php

return [
    /*
     * Supported revisionable models.
     *
     * You can use either a key => class map (recommended for API input),
     * or a simple list of class names.
     *
     * Example:
     * 'post' => App\Containers\AppSection\Blog\Models\Post::class,
     */
    'supported' => [
        'post' => \App\Containers\AppSection\Blog\Models\Post::class,
        'page' => \App\Containers\AppSection\Page\Models\Page::class,
    ],

    'default_per_page' => 20,
    'max_per_page' => 200,
];
