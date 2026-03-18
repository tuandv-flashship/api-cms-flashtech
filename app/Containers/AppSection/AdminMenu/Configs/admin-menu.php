<?php

/*
|--------------------------------------------------------------------------
| Admin Sidebar Menu (Default Data)
|--------------------------------------------------------------------------
|
| Flat/nested menu items that mirror the Botble CMS sidebar layout.
| Each item is filtered at runtime by AdminMenu::forUser() based on
| the user's permissions.
|
| Fields:
|   - id            : Unique identifier for the menu item
|   - key           : Translation key (used for i18n lookup)
|   - name          : Display name in the original language (English)
|   - description   : Description in the original language (English)
|   - icon          : Tabler icon class
|   - route         : Frontend route path
|   - priority      : Sort order (lower = higher)
|   - permissions   : Permission strings for access control
|   - children_display : "sidebar" (default) or "panel"
|   - children      : Nested child items
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
        'key' => 'admin_menu.dashboard',
        'name' => 'Dashboard',
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
        'key' => 'admin_menu.pages',
        'name' => 'Pages',
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
        'key' => 'admin_menu.blog',
        'name' => 'Blog',
        'icon' => 'ti ti-article',
        'route' => null,
        'priority' => 3,
        'permissions' => ['posts.index', 'categories.index', 'tags.index'],
        'children_display' => 'sidebar',
        'children' => [
            [
                'id' => 'cms-plugins-blog-post',
                'key' => 'admin_menu.posts',
                'name' => 'Posts',
                'icon' => 'ti ti-file-text',
                'route' => '/blog/posts',
                'priority' => 10,
                'permissions' => ['posts.index'],
            ],
            [
                'id' => 'cms-plugins-blog-categories',
                'key' => 'admin_menu.categories',
                'name' => 'Categories',
                'icon' => 'ti ti-folder',
                'route' => '/blog/categories',
                'priority' => 20,
                'permissions' => ['categories.index'],
            ],
            [
                'id' => 'cms-plugins-blog-tags',
                'key' => 'admin_menu.tags',
                'name' => 'Tags',
                'icon' => 'ti ti-tag',
                'route' => '/blog/tags',
                'priority' => 30,
                'permissions' => ['tags.index'],
            ],
            [
                'id' => 'cms-plugins-blog-reports',
                'key' => 'admin_menu.reports',
                'name' => 'Reports',
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
        'key' => 'admin_menu.galleries',
        'name' => 'Galleries',
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
        'key' => 'admin_menu.members',
        'name' => 'Members',
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
        'key' => 'admin_menu.custom_fields',
        'name' => 'Custom Fields',
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
        'key' => 'admin_menu.media',
        'name' => 'Media',
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
        'key' => 'admin_menu.appearance',
        'name' => 'Appearance',
        'icon' => 'ti ti-brush',
        'route' => null,
        'priority' => 2000,
        'permissions' => ['menus.index'],
        'children_display' => 'sidebar',
        'children' => [
            [
                'id' => 'cms-core-menu',
                'key' => 'appearance.menus',
                'name' => 'Menus',
                'icon' => 'ti ti-tournament',
                'route' => '/appearance/menus',
                'priority' => 2,
                'permissions' => ['menus.index'],
            ],
            [
                'id' => 'cms-core-admin-menu',
                'key' => 'appearance.admin_menus',
                'name' => 'Admin Menus',
                'icon' => 'ti ti-tournament',
                'route' => '/appearance/admin-menus',
                'priority' => 3,
                'permissions' => ['admin-menus.index'],
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
        'key' => 'admin_menu.tools',
        'name' => 'Tools',
        'icon' => 'ti ti-tool',
        'route' => null,
        'priority' => 9000,
        'permissions' => ['core.tools'],
        'children_display' => 'sidebar',
        'children' => [
            [
                'id' => 'cms-tools-data-synchronize',
                'key' => 'admin_menu.data_synchronize',
                'name' => 'Data Synchronize',
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
        'key' => 'admin_menu.settings',
        'name' => 'Settings',
        'icon' => 'ti ti-settings',
        'route' => '/settings',
        'priority' => 9999,
        'permissions' => ['settings.common'],
        'children_display' => 'panel',
        'children' => [
            // ── Section: General ──────────────────────────────────
            [
                'id' => 'cms-settings-general',
                'key' => 'admin_menu.general_settings',
                'name' => 'General',
                'description' => 'View and update general settings and activate license',
                'icon' => 'ti ti-settings',
                'route' => '/settings/general',
                'priority' => 10,
                'permissions' => ['settings.common'],
                'section' => 'General',
            ],
            [
                'id' => 'cms-settings-email-rules',
                'key' => 'admin_menu.email_rules',
                'name' => 'Email Rules',
                'description' => 'Configure email rules for verification',
                'icon' => 'ti ti-mail-check',
                'route' => '/settings/email-rules',
                'priority' => 20,
                'permissions' => ['settings.common'],
                'section' => 'General',
            ],
            [
                'id' => 'cms-settings-phone-number',
                'key' => 'admin_menu.phone_number',
                'name' => 'Phone Number',
                'description' => 'Configure phone number field settings',
                'icon' => 'ti ti-phone',
                'route' => '/settings/phone-number',
                'priority' => 30,
                'permissions' => ['settings.common'],
                'section' => 'General',
            ],
            [
                'id' => 'cms-settings-media',
                'key' => 'admin_menu.media_settings',
                'name' => 'Media',
                'description' => 'View and update media settings',
                'icon' => 'ti ti-photo',
                'route' => '/settings/media',
                'priority' => 40,
                'permissions' => ['settings.common'],
                'section' => 'General',
            ],
            [
                'id' => 'cms-settings-languages',
                'key' => 'admin_menu.languages',
                'name' => 'Languages',
                'description' => 'View and update your website languages',
                'icon' => 'ti ti-language',
                'route' => '/settings/languages',
                'priority' => 50,
                'permissions' => ['languages.index'],
                'section' => 'General',
            ],
            [
                'id' => 'cms-settings-admin-appearance',
                'key' => 'admin_menu.admin_appearance',
                'name' => 'Admin Appearance',
                'description' => 'View and update logo, favicon, layout, ...',
                'icon' => 'ti ti-palette',
                'route' => '/settings/admin-appearance',
                'priority' => 60,
                'permissions' => ['settings.common'],
                'section' => 'General',
            ],
            [
                'id' => 'cms-settings-cache',
                'key' => 'admin_menu.cache',
                'name' => 'Cache',
                'description' => 'Configure cache for speed optimization',
                'icon' => 'ti ti-database',
                'route' => '/settings/cache',
                'priority' => 70,
                'permissions' => ['settings.common'],
                'section' => 'General',
            ],
            [
                'id' => 'cms-settings-speed-optimization',
                'key' => 'admin_menu.speed_optimization',
                'name' => 'Speed Optimization',
                'description' => 'Compress HTML output, inline CSS, remove comments...',
                'icon' => 'ti ti-bolt',
                'route' => '/settings/speed-optimization',
                'priority' => 80,
                'permissions' => ['settings.common'],
                'section' => 'General',
            ],

            // ── Section: Localization ─────────────────────────────
            [
                'id' => 'cms-settings-locales',
                'key' => 'admin_menu.locales',
                'name' => 'Locales',
                'description' => 'View, download and import locales',
                'icon' => 'ti ti-world',
                'route' => '/settings/locales',
                'priority' => 90,
                'permissions' => ['languages.index'],
                'section' => 'Localization',
            ],
            [
                'id' => 'cms-settings-other-translations',
                'key' => 'admin_menu.other_translations',
                'name' => 'Other Translations',
                'description' => 'Manage the other translations (admin, plugins, packages...)',
                'icon' => 'ti ti-message-language',
                'route' => '/settings/other-translations',
                'priority' => 100,
                'permissions' => ['translations.index'],
                'section' => 'Localization',
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
        'key' => 'admin_menu.platform_admin',
        'name' => 'Platform Administration',
        'icon' => 'ti ti-user-shield',
        'route' => '/system',
        'priority' => 10000,
        'permissions' => ['core.system'],
        'children_display' => 'panel',
        'children' => [
            [
                'id' => 'cms-system-users',
                'key' => 'admin_menu.users',
                'name' => 'Users',
                'description' => 'Manage admin users and their permissions',
                'icon' => 'ti ti-user',
                'route' => '/system/users',
                'priority' => 10,
                'permissions' => ['users.index'],
                'section' => 'Users & Permissions',
            ],
            [
                'id' => 'cms-system-roles',
                'key' => 'admin_menu.roles',
                'name' => 'Roles & Permissions',
                'description' => 'Manage roles and assign permissions',
                'icon' => 'ti ti-lock',
                'route' => '/system/roles',
                'priority' => 20,
                'permissions' => ['roles.index'],
                'section' => 'Users & Permissions',
            ],
            [
                'id' => 'cms-system-audit-logs',
                'key' => 'admin_menu.audit_logs',
                'name' => 'Audit Logs',
                'description' => 'Track admin activities and changes',
                'icon' => 'ti ti-clipboard-list',
                'route' => '/system/audit-logs',
                'priority' => 30,
                'permissions' => ['audit-log.index'],
                'section' => 'Monitoring',
            ],
            [
                'id' => 'cms-system-request-logs',
                'key' => 'admin_menu.request_logs',
                'name' => 'Request Logs',
                'description' => 'Monitor API request logs and errors',
                'icon' => 'ti ti-report-analytics',
                'route' => '/system/request-logs',
                'priority' => 40,
                'permissions' => ['request-log.index'],
                'section' => 'Monitoring',
            ],
            [
                'id' => 'cms-system-info',
                'key' => 'admin_menu.system_info',
                'name' => 'System Information',
                'description' => 'View system environment and configuration',
                'icon' => 'ti ti-info-circle',
                'route' => '/system/info',
                'priority' => 50,
                'permissions' => ['system.info'],
                'section' => 'System',
            ],
            [
                'id' => 'cms-system-cache',
                'key' => 'admin_menu.system_cache',
                'name' => 'Cache Management',
                'description' => 'Clear and manage application cache',
                'icon' => 'ti ti-database',
                'route' => '/system/cache',
                'priority' => 60,
                'permissions' => ['system.cache'],
                'section' => 'System',
            ],
        ],
    ],
];
