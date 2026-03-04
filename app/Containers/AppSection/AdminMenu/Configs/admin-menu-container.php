<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum Menu Depth
    |--------------------------------------------------------------------------
    |
    | The maximum nesting depth allowed for admin menu items.
    | Level 1 = root items, Level 2 = children, Level 3 = grandchildren.
    |
    */
    'max_depth' => (int) env('ADMIN_MENU_MAX_DEPTH', 3),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | How long to cache the admin menu per user+locale.
    | Set to 0 to disable caching entirely.
    |
    */
    'cache_ttl_seconds' => (int) env('ADMIN_MENU_CACHE_TTL', 3600),
];
