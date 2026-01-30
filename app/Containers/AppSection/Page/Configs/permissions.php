<?php

return [
    [
        'name' => 'Pages',
        'flag' => 'plugins.page',
        'parent_flag' => 'core.cms',
    ],
    [
        'name' => 'Pages',
        'flag' => 'pages.index',
        'parent_flag' => 'plugins.page',
    ],
    [
        'name' => 'Create',
        'flag' => 'pages.create',
        'parent_flag' => 'pages.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'pages.edit',
        'parent_flag' => 'pages.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'pages.destroy',
        'parent_flag' => 'pages.index',
    ],
];
