<?php

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Table\Abstracts\ColumnDefinition;

return [
    /*
    |--------------------------------------------------------------------------
    | Table Module Configuration
    |--------------------------------------------------------------------------
    |
    | 'max_bulk_items': Max IDs per bulk request (prevents abuse/timeouts).
    |
    | 'models': Registry of table-enabled models. Each key is used in API
    | requests (e.g. ?model=post). Minimum: 'model' + 'permission_prefix'.
    | Everything else is auto-detected from model casts/fillable.
    | See HasTableConfig trait for model-level overrides.
    |
    */

    'max_bulk_items' => 100,

    'models' => [
        'post' => [
            'model' => Post::class,
            'permission_prefix' => 'posts',
            'api_prefix' => '/v1/blog/posts',     // API route for type=action
            'fe_prefix' => '/blog/posts',           // FE route for type=link
            'default_sort' => ['key' => 'created_at', 'direction' => 'desc'],
            'columns' => [
                ColumnDefinition::number('views', 'table::columns.views')
                    ->visible(false)->width(80)->align('right'),
            ],
        ],

        'category' => [
            'model' => Category::class,
            'permission_prefix' => 'categories',
            'api_prefix' => '/v1/blog/categories',
            'fe_prefix' => '/blog/categories',
        ],

        'tag' => [
            'model' => Tag::class,
            'permission_prefix' => 'tags',
            'api_prefix' => '/v1/blog/tags',
            'fe_prefix' => '/blog/tags',
        ],

        'page' => [
            'model' => Page::class,
            'permission_prefix' => 'pages',
            'api_prefix' => '/v1/pages',
            'fe_prefix' => '/pages',
        ],
    ],
];
