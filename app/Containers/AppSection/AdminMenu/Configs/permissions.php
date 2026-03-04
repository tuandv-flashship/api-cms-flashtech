<?php

return [
    [
        'name' => 'Admin Menu',
        'flag' => 'admin-menus.index',
        'parent_flag' => 'core.system',
    ],
    [
        'name' => 'Show',
        'flag' => 'admin-menus.show',
        'parent_flag' => 'admin-menus.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'admin-menus.create',
        'parent_flag' => 'admin-menus.index',
    ],
    [
        'name' => 'Update',
        'flag' => 'admin-menus.update',
        'parent_flag' => 'admin-menus.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'admin-menus.delete',
        'parent_flag' => 'admin-menus.index',
    ],
];
