<?php

return [
    [
        'name' => 'Menus',
        'flag' => 'plugins.menu',
        'parent_flag' => 'core.cms',
    ],
    [
        'name' => 'Menus',
        'flag' => 'menus.index',
        'parent_flag' => 'plugins.menu',
    ],
    [
        'name' => 'Show',
        'flag' => 'menus.show',
        'parent_flag' => 'menus.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'menus.create',
        'parent_flag' => 'menus.index',
    ],
    [
        'name' => 'Update',
        'flag' => 'menus.update',
        'parent_flag' => 'menus.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'menus.delete',
        'parent_flag' => 'menus.index',
    ],
];
