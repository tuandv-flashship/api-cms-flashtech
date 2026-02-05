<?php

return [
    [
        'name' => 'Members',
        'flag' => 'members.index',
        'parent_flag' => 'core.cms',
    ],
    [
        'name' => 'Create',
        'flag' => 'members.create',
        'parent_flag' => 'members.index',
    ],
    [
        'name' => 'View',
        'flag' => 'members.show',
        'parent_flag' => 'members.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'members.edit',
        'parent_flag' => 'members.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'members.destroy',
        'parent_flag' => 'members.index',
    ],
];
