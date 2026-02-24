<?php

/*
|--------------------------------------------------------------------------
| Admin Sidebar Menu
|--------------------------------------------------------------------------
|
| Flat/nested menu items that mirror the Botble CMS sidebar layout.
| Each item is filtered at runtime by AdminMenu::forUser() based on
| the user's permissions.
|
| children_display:
|   - "sidebar"  (default) → children rendered as sidebar sub-menu items
|   - "panel"              → children rendered as panel sections on the destination page
|
| Reference (from Botble CMS admin sidebar):
|
|   Bảng điều khiển          Dashboard
|   Trang                   Pages
|   Blog ▸                  Blog  → Posts, Categories, Tags, Reports
|   Thư viện ảnh             Galleries
|   Thành viên               Members
|   Trường tùy chỉnh         Custom Fields
|   Quản lý tập tin          Media
|   Hiển thị ▸               Appearance  → Menus
|   Công cụ ▸                Tools  → Import/Export Data
|   Cài đặt                  Settings  (panel: Languages, Translations)
|   Quản trị hệ thống        Platform Admin  (panel: Users, Roles, System …)
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Dashboard  (always visible to authenticated users)
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-core-dashboard',
        'name' => 'admin_menu.dashboard',
        'icon' => 'ti ti-home',
        'route' => '/dashboard',
        'priority' => 1,
        'permissions' => null,
    ],

    /*
    |----------------------------------------------------------------------
    | Pages
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-core-page',
        'name' => 'admin_menu.pages',
        'icon' => 'ti ti-notebook',
        'route' => '/pages',
        'priority' => 2,
        'permissions' => ['pages.index'],
    ],

    /*
    |----------------------------------------------------------------------
    | Blog  (sidebar children: Posts, Categories, Tags, Reports)
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-plugins-blog',
        'name' => 'admin_menu.blog',
        'icon' => 'ti ti-article',
        'route' => null,
        'priority' => 3,
        'permissions' => ['posts.index', 'categories.index', 'tags.index'],
        'children_display' => 'sidebar',
        'children' => [
            [
                'id' => 'cms-plugins-blog-post',
                'name' => 'admin_menu.posts',
                'icon' => 'ti ti-file-text',
                'route' => '/blog/posts',
                'priority' => 10,
                'permissions' => ['posts.index'],
            ],
            [
                'id' => 'cms-plugins-blog-categories',
                'name' => 'admin_menu.categories',
                'icon' => 'ti ti-folder',
                'route' => '/blog/categories',
                'priority' => 20,
                'permissions' => ['categories.index'],
            ],
            [
                'id' => 'cms-plugins-blog-tags',
                'name' => 'admin_menu.tags',
                'icon' => 'ti ti-tag',
                'route' => '/blog/tags',
                'priority' => 30,
                'permissions' => ['tags.index'],
            ],
            [
                'id' => 'cms-plugins-blog-reports',
                'name' => 'admin_menu.reports',
                'icon' => 'ti ti-chart-bar',
                'route' => '/blog/reports',
                'priority' => 40,
                'permissions' => ['reports.index'],
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Galleries
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-plugins-gallery',
        'name' => 'admin_menu.galleries',
        'icon' => 'ti ti-camera',
        'route' => '/galleries',
        'priority' => 5,
        'permissions' => ['galleries.index'],
    ],

    /*
    |----------------------------------------------------------------------
    | Members
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-core-member',
        'name' => 'admin_menu.members',
        'icon' => 'ti ti-users',
        'route' => '/members',
        'priority' => 50,
        'permissions' => ['members.index'],
    ],

    /*
    |----------------------------------------------------------------------
    | Custom Fields
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-plugins-custom-field',
        'name' => 'admin_menu.custom_fields',
        'icon' => 'ti ti-forms',
        'route' => '/custom-fields',
        'priority' => 100,
        'permissions' => ['custom-fields.index'],
    ],

    /*
    |----------------------------------------------------------------------
    | Media
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-core-media',
        'name' => 'admin_menu.media',
        'icon' => 'ti ti-folder',
        'route' => '/media',
        'priority' => 999,
        'permissions' => ['media.index'],
    ],

    /*
    |----------------------------------------------------------------------
    | Appearance  (sidebar children: Menus)
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-core-appearance',
        'name' => 'admin_menu.appearance',
        'icon' => 'ti ti-brush',
        'route' => null,
        'priority' => 2000,
        'permissions' => ['menus.index'],
        'children_display' => 'sidebar',
        'children' => [
            [
                'id' => 'cms-core-menu',
                'name' => 'admin_menu.menus',
                'icon' => 'ti ti-tournament',
                'route' => '/menus',
                'priority' => 2,
                'permissions' => ['menus.index'],
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Tools  (sidebar children: Data Synchronize)
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-core-tools',
        'name' => 'admin_menu.tools',
        'icon' => 'ti ti-tool',
        'route' => null,
        'priority' => 9000,
        'permissions' => ['core.tools'],
        'children_display' => 'sidebar',
        'children' => [
            [
                'id' => 'cms-tools-data-synchronize',
                'name' => 'admin_menu.data_synchronize',
                'icon' => 'ti ti-arrows-exchange',
                'route' => '/tools/data-synchronize',
                'priority' => 10,
                'permissions' => ['tools.data-synchronize'],
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Settings  (panel children — rendered on /settings page)
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-core-settings',
        'name' => 'admin_menu.settings',
        'icon' => 'ti ti-settings',
        'route' => '/settings',
        'priority' => 9999,
        'permissions' => ['settings.common'],
        'children_display' => 'panel',
        'children' => [
            [
                'id' => 'cms-settings-languages',
                'name' => 'admin_menu.languages',
                'icon' => 'ti ti-language',
                'route' => '/settings/languages',
                'priority' => 10,
                'permissions' => ['languages.index'],
                'description' => 'admin_menu.languages_desc',
            ],
            [
                'id' => 'cms-settings-translations',
                'name' => 'admin_menu.translations',
                'icon' => 'ti ti-a-b',
                'route' => '/settings/translations',
                'priority' => 20,
                'permissions' => ['translations.index'],
                'description' => 'admin_menu.translations_desc',
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Platform Administration  (panel children — rendered on /system page)
    |----------------------------------------------------------------------
    */
    [
        'id' => 'cms-core-system',
        'name' => 'admin_menu.platform_admin',
        'icon' => 'ti ti-user-shield',
        'route' => '/system',
        'priority' => 10000,
        'permissions' => ['core.system'],
        'children_display' => 'panel',
        'children' => [
            [
                'id' => 'cms-system-users',
                'name' => 'admin_menu.users',
                'icon' => 'ti ti-user',
                'route' => '/system/users',
                'priority' => 10,
                'permissions' => ['users.index'],
                'description' => 'admin_menu.users_desc',
            ],
            [
                'id' => 'cms-system-roles',
                'name' => 'admin_menu.roles',
                'icon' => 'ti ti-lock',
                'route' => '/system/roles',
                'priority' => 20,
                'permissions' => ['roles.index'],
                'description' => 'admin_menu.roles_desc',
            ],
            [
                'id' => 'cms-system-audit-logs',
                'name' => 'admin_menu.audit_logs',
                'icon' => 'ti ti-clipboard-list',
                'route' => '/system/audit-logs',
                'priority' => 30,
                'permissions' => ['audit-log.index'],
                'description' => 'admin_menu.audit_logs_desc',
            ],
            [
                'id' => 'cms-system-request-logs',
                'name' => 'admin_menu.request_logs',
                'icon' => 'ti ti-report-analytics',
                'route' => '/system/request-logs',
                'priority' => 40,
                'permissions' => ['request-log.index'],
                'description' => 'admin_menu.request_logs_desc',
            ],
            [
                'id' => 'cms-system-info',
                'name' => 'admin_menu.system_info',
                'icon' => 'ti ti-info-circle',
                'route' => '/system/info',
                'priority' => 50,
                'permissions' => ['system.info'],
                'description' => 'admin_menu.system_info_desc',
            ],
            [
                'id' => 'cms-system-cache',
                'name' => 'admin_menu.system_cache',
                'icon' => 'ti ti-database',
                'route' => '/system/cache',
                'priority' => 60,
                'permissions' => ['system.cache'],
                'description' => 'admin_menu.system_cache_desc',
            ],
        ],
    ],
];
