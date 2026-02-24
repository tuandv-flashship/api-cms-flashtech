<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Menu Cache TTL
    |--------------------------------------------------------------------------
    |
    | Time-to-live in seconds for the per-user admin menu cache.
    | Set to 0 to disable caching entirely.
    |
    */
    'ttl_seconds' => env('ADMIN_MENU_CACHE_TTL_SECONDS', 3600),
];
