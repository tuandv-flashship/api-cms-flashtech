<?php

return [
    [
        'name' => 'Translations',
        'flag' => 'translations.index',
        'parent_flag' => 'settings.common',
    ],
    [
        'name' => 'Create Locale',
        'flag' => 'translations.create',
        'parent_flag' => 'translations.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'translations.edit',
        'parent_flag' => 'translations.index',
    ],
    [
        'name' => 'Delete Locale',
        'flag' => 'translations.destroy',
        'parent_flag' => 'translations.index',
    ],
    [
        'name' => 'Download Locale',
        'flag' => 'translations.download',
        'parent_flag' => 'translations.index',
    ],
];
