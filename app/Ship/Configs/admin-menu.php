<?php

return [
    [
        'id' => 'content',
        'name' => 'admin_menu.content',
        'icon' => 'ti ti-layout-grid',
        'route' => null,
        'priority' => 40,
        'permissions' => ['core.cms'],
        'children' => [
            [
                'id' => 'content.pages',
                'name' => 'admin_menu.pages',
                'icon' => 'ti ti-file-text',
                'route' => '/pages',
                'permissions' => ['pages.index'],
            ],
            [
                'id' => 'content.blog',
                'name' => 'admin_menu.blog',
                'icon' => 'ti ti-article',
                'route' => '/blog/posts',
                'permissions' => ['posts.index', 'categories.index', 'tags.index'],
            ],
            [
                'id' => 'content.menus',
                'name' => 'admin_menu.menus',
                'icon' => 'ti ti-menu-2',
                'route' => '/menus',
                'permissions' => ['menus.index'],
            ],
        ],
    ],
];
