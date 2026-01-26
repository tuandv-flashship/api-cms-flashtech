<?php

return [
    'general' => [
        'pattern' => '--slug--',
        'supported' => [],
        'prefixes' => [],
        'disable_preview' => [],
        'slug_generated_columns' => [],
        'enable_slug_translator' => env('CMS_ENABLE_SLUG_TRANSLATOR', false),
        'public_single_ending_url' => env('SLUG_PUBLIC_SINGLE_ENDING_URL', ''),
    ],
];
