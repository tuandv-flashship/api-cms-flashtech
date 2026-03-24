<?php

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Data\Repositories\PageRepository;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\UI\API\Requests\CreatePageRequest;
use App\Containers\AppSection\Page\UI\API\Requests\UpdatePageRequest;
use App\Containers\AppSection\Page\UI\API\Requests\UpdatePageTranslationRequest;
use App\Containers\AppSection\Table\Abstracts\ColumnDefinition;
use App\Containers\AppSection\Table\Abstracts\FormFieldDefinition;

/*
|--------------------------------------------------------------------------
| Page Container — Table & Form Metadata
|--------------------------------------------------------------------------
*/

return [
    'page' => [
        'model'             => Page::class,
        'repository'        => PageRepository::class,
        'permission_prefix' => 'pages',
        'permission'        => 'pages.index',
        'api_prefix'        => '/v1/pages',
        'fe_prefix'         => '/pages',

        'columns' => [
            ColumnDefinition::make('status', 'table::columns.status')
                ->searchable()->enum(ContentStatus::class)->width(100)->priority(2),
        ],

        'forms' => [
            'create' => [
                'request'    => CreatePageRequest::class,
                'permission' => 'pages.create',
                'submit'     => ['method' => 'POST', 'url' => '/v1/pages'],
                'groups'     => [
                    ['key' => 'basic', 'label' => 'table::groups.basic', 'order' => 0],
                    ['key' => 'content', 'label' => 'table::groups.content', 'order' => 1],
                    ['key' => 'settings', 'label' => 'table::groups.settings', 'order' => 2],
                ],
                'overrides' => [
                    FormFieldDefinition::text('name')->group('basic')->order(0),
                    FormFieldDefinition::textarea('description')->group('basic')->order(1)->colSpan(2),
                    FormFieldDefinition::text('slug')->group('basic')->order(2),
                    FormFieldDefinition::textarea('content')->group('content')->order(0)->colSpan(2),
                    FormFieldDefinition::select('status')->group('settings')->order(0),
                    FormFieldDefinition::text('image')->group('settings')->order(1),
                    FormFieldDefinition::text('template')->group('settings')->order(2),
                ],
            ],
            'update' => [
                'request'    => UpdatePageRequest::class,
                'permission' => 'pages.edit',
                'submit'     => ['method' => 'PATCH', 'url' => '/v1/pages/{id}'],
            ],
            'translate' => [
                'request'    => UpdatePageTranslationRequest::class,
                'permission' => 'pages.edit',
                'submit'     => ['method' => 'PUT', 'url' => '/v1/pages/{id}/translations'],
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
];
