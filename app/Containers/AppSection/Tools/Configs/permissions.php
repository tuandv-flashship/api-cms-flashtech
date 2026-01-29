<?php

return [
    [
        'name' => 'Export Posts',
        'flag' => 'posts.export',
        'parent_flag' => 'tools.data-synchronize',
    ],
    [
        'name' => 'Import Posts',
        'flag' => 'posts.import',
        'parent_flag' => 'tools.data-synchronize',
    ],
    [
        'name' => 'Export Post Translations',
        'flag' => 'post-translations.export',
        'parent_flag' => 'tools.data-synchronize',
    ],
    [
        'name' => 'Import Post Translations',
        'flag' => 'post-translations.import',
        'parent_flag' => 'tools.data-synchronize',
    ],
    [
        'name' => 'Export Other Translations',
        'flag' => 'other-translations.export',
        'parent_flag' => 'tools.data-synchronize',
    ],
    [
        'name' => 'Import Other Translations',
        'flag' => 'other-translations.import',
        'parent_flag' => 'tools.data-synchronize',
    ],
];
