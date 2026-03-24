<?php

use App\Containers\AppSection\Blog\Data\Repositories\CategoryRepository;
use App\Containers\AppSection\Blog\Data\Repositories\PostRepository;
use App\Containers\AppSection\Blog\Data\Repositories\TagRepository;
use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\UI\API\Requests\CreateCategoryRequest;
use App\Containers\AppSection\Blog\UI\API\Requests\CreatePostRequest;
use App\Containers\AppSection\Blog\UI\API\Requests\CreateTagRequest;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateCategoryRequest;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateCategoryTranslationRequest;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdatePostRequest;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdatePostTranslationRequest;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateTagRequest;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateTagTranslationRequest;
use App\Containers\AppSection\Table\Abstracts\ColumnDefinition;
use App\Containers\AppSection\Table\Abstracts\FormFieldDefinition;

/*
|--------------------------------------------------------------------------
| Blog Container — Table & Form Metadata
|--------------------------------------------------------------------------
|
| Registered models for table-meta and form-meta APIs.
| File convention: table-models.php → auto-discovered by Table container.
|
*/

return [
    'post' => [
        'model'             => Post::class,
        'repository'        => PostRepository::class,
        'permission_prefix' => 'posts',
        'permission'        => 'posts.index',
        'api_prefix'        => '/v1/blog/posts',
        'fe_prefix'         => '/blog/posts',
        'default_sort'      => ['key' => 'created_at', 'direction' => 'desc'],
        'pagination'        => ['default_limit' => 15, 'limits' => [15, 30, 50, 100]],

        'columns' => [
            ColumnDefinition::make('status', 'table::columns.status')
                ->searchable()->enum(ContentStatus::class)->width(100)->priority(2),
            ColumnDefinition::number('views', 'table::columns.views')
                ->visible(false)->width(80)->align('right'),
        ],

        'forms' => [
            'create' => [
                'request'    => CreatePostRequest::class,
                'permission' => 'posts.create',
                'submit'     => ['method' => 'POST', 'url' => '/v1/blog/posts'],
                'groups'     => [
                    ['key' => 'basic', 'label' => 'table::groups.basic', 'order' => 0],
                    ['key' => 'content', 'label' => 'table::groups.content', 'order' => 1],
                    ['key' => 'settings', 'label' => 'table::groups.settings', 'order' => 2],
                    ['key' => 'media', 'label' => 'table::groups.media', 'order' => 3],
                ],
                'overrides' => [
                    FormFieldDefinition::text('name')->group('basic')->order(0),
                    FormFieldDefinition::textarea('description')->group('basic')->order(1)->colSpan(2),
                    FormFieldDefinition::text('slug')->group('basic')->order(2),
                    FormFieldDefinition::textarea('content')->group('content')->order(0)->colSpan(2),
                    FormFieldDefinition::select('status')->group('settings')->order(0),
                    FormFieldDefinition::boolean('is_featured')->group('settings')->order(1),
                    FormFieldDefinition::text('image')->group('media')->order(0),
                    FormFieldDefinition::text('banner_image')->group('media')->order(1),
                ],
            ],
            'update' => [
                'request'    => UpdatePostRequest::class,
                'permission' => 'posts.edit',
                'submit'     => ['method' => 'PATCH', 'url' => '/v1/blog/posts/{id}'],
            ],
            'translate' => [
                'request'    => UpdatePostTranslationRequest::class,
                'permission' => 'posts.edit',
                'submit'     => ['method' => 'PUT', 'url' => '/v1/blog/posts/{id}/translations'],
                'groups'     => [
                    ['key' => 'translation', 'label' => 'table::groups.basic', 'order' => 0],
                ],
                'overrides' => [
                    FormFieldDefinition::hidden('lang_code')->group('translation')->order(0),
                    FormFieldDefinition::text('name')->group('translation')->order(1),
                    FormFieldDefinition::textarea('description')->group('translation')->order(2)->colSpan(2),
                    FormFieldDefinition::textarea('content')->group('translation')->order(3)->colSpan(2),
                    FormFieldDefinition::text('slug')->group('translation')->order(4),
                ],
            ],
        ],
    ],

    'category' => [
        'model'             => Category::class,
        'repository'        => CategoryRepository::class,
        'permission_prefix' => 'categories',
        'permission'        => 'categories.index',
        'api_prefix'        => '/v1/blog/categories',
        'fe_prefix'         => '/blog/categories',

        'forms' => [
            'create' => [
                'request'    => CreateCategoryRequest::class,
                'permission' => 'categories.create',
                'submit'     => ['method' => 'POST', 'url' => '/v1/blog/categories'],
                'groups'     => [
                    ['key' => 'basic', 'label' => 'table::groups.basic', 'order' => 0],
                    ['key' => 'settings', 'label' => 'table::groups.settings', 'order' => 1],
                ],
                'overrides' => [
                    FormFieldDefinition::text('name')->group('basic')->order(0),
                    FormFieldDefinition::textarea('description')->group('basic')->order(1)->colSpan(2),
                    FormFieldDefinition::icon('icon')->group('basic')->order(2),
                    FormFieldDefinition::text('slug')->group('basic')->order(3),
                    FormFieldDefinition::select('status')->group('settings')->order(0),
                    FormFieldDefinition::relation('parent_id', 'table::fields.parent_id')
                        ->endpoint('/v1/blog/categories')
                        ->labelField('name')->valueField('id')
                        ->group('settings')->order(1),
                    FormFieldDefinition::number('order')->group('settings')->order(2)->default(0),
                    FormFieldDefinition::boolean('is_featured')->group('settings')->order(3),
                    FormFieldDefinition::boolean('is_default')->group('settings')->order(4),
                ],
            ],
            'update' => [
                'request'    => UpdateCategoryRequest::class,
                'permission' => 'categories.edit',
                'submit'     => ['method' => 'PATCH', 'url' => '/v1/blog/categories/{id}'],
            ],
            'translate' => [
                'request'    => UpdateCategoryTranslationRequest::class,
                'permission' => 'categories.edit',
                'submit'     => ['method' => 'PUT', 'url' => '/v1/blog/categories/{id}/translations'],
                'groups'     => [
                    ['key' => 'translation', 'label' => 'table::groups.basic', 'order' => 0],
                ],
                'overrides' => [
                    FormFieldDefinition::hidden('lang_code')->group('translation')->order(0),
                    FormFieldDefinition::text('name')->group('translation')->order(1),
                    FormFieldDefinition::textarea('description')->group('translation')->order(2)->colSpan(2),
                    FormFieldDefinition::text('slug')->group('translation')->order(3),
                ],
            ],
        ],
    ],

    'tag' => [
        'model'             => Tag::class,
        'repository'        => TagRepository::class,
        'permission_prefix' => 'tags',
        'permission'        => 'tags.index',
        'api_prefix'        => '/v1/blog/tags',
        'fe_prefix'         => '/blog/tags',

        'forms' => [
            'create' => [
                'request'    => CreateTagRequest::class,
                'permission' => 'tags.create',
                'submit'     => ['method' => 'POST', 'url' => '/v1/blog/tags'],
                'groups'     => [
                    ['key' => 'basic', 'label' => 'table::groups.basic', 'order' => 0],
                ],
                'overrides' => [
                    FormFieldDefinition::text('name')->group('basic')->order(0),
                    FormFieldDefinition::textarea('description')->group('basic')->order(1)->colSpan(2),
                    FormFieldDefinition::select('status')->group('basic')->order(2),
                    FormFieldDefinition::text('slug')->group('basic')->order(3),
                ],
            ],
            'update' => [
                'request'    => UpdateTagRequest::class,
                'permission' => 'tags.edit',
                'submit'     => ['method' => 'PATCH', 'url' => '/v1/blog/tags/{id}'],
            ],
            'translate' => [
                'request'    => UpdateTagTranslationRequest::class,
                'permission' => 'tags.edit',
                'submit'     => ['method' => 'PUT', 'url' => '/v1/blog/tags/{id}/translations'],
                'groups'     => [
                    ['key' => 'translation', 'label' => 'table::groups.basic', 'order' => 0],
                ],
                'overrides' => [
                    FormFieldDefinition::hidden('lang_code')->group('translation')->order(0),
                    FormFieldDefinition::text('name')->group('translation')->order(1),
                    FormFieldDefinition::textarea('description')->group('translation')->order(2)->colSpan(2),
                    FormFieldDefinition::text('slug')->group('translation')->order(3),
                ],
            ],
        ],
    ],
];
