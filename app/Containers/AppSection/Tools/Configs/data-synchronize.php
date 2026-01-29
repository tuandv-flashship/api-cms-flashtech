<?php

return [
    'storage' => [
        'disk' => env('DATA_SYNCHRONIZE_DISK', 'local'),
        'path' => env('DATA_SYNCHRONIZE_PATH', 'data-synchronize'),
    ],
    'max_file_size_kb' => env('DATA_SYNCHRONIZE_MAX_FILE_SIZE_KB', 1024),
    'extensions' => ['csv', 'xlsx'],
    'mime_types' => [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ],
    'formats' => ['csv', 'xlsx'],
    'imports' => [
        'posts_chunk_size' => 50,
        'post_translations_chunk_size' => 100,
        'other_translations_chunk_size' => 1000,
    ],
];
