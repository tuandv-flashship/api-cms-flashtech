<?php

return [
    'types' => [
        'posts' => [
            'label' => 'Posts',
            'export_description' => 'Export posts to CSV/Excel file.',
            'import_description' => 'Import posts from a CSV/Excel file.',
        ],
        'pages' => [
            'label' => 'Pages',
            'export_description' => 'Export pages to CSV/Excel file.',
            'import_description' => 'Import pages from a CSV/Excel file.',
        ],
        'post-translations' => [
            'label' => 'Post Translations',
            'export_description' => 'Export post translations to CSV/Excel file.',
            'import_description' => 'Import post translations from a CSV/Excel file.',
        ],
        'page-translations' => [
            'label' => 'Page Translations',
            'export_description' => 'Export page translations to CSV/Excel file.',
            'import_description' => 'Import page translations from a CSV/Excel file.',
        ],
        'other-translations' => [
            'label' => 'Other Translations',
            'export_description' => 'Export other translations to CSV/Excel file.',
            'import_description' => 'Import other translations from a CSV/Excel file.',
        ],
    ],
    'filters' => [
        'limit' => 'Limit',
        'status' => 'Status',
        'is_featured' => 'Is Featured',
        'category' => 'Category',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'template' => 'Template',
        'limit_placeholder' => 'Leave empty to export all',
    ],
    'columns' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'content' => 'Content',
        'status' => 'Status',
        'image' => 'Image',
        'template' => 'Template',
        'views' => 'Views',
        'url' => 'URL',
        'categories' => 'Categories',
        'tags' => 'Tags',
        'is_featured' => 'Is Featured',
        'format_type' => 'Format Type',
        'id' => 'ID',
    ],
    'rules' => [
        'required' => 'is required',
        'string_type' => 'must be a string',
        'integer_type' => 'must be an integer',
        'max_chars' => 'of up to :max characters',
        'may_blank' => 'may be left blank',
        'allowed_values' => 'must be one of the allowed values',
        'accepts_value' => 'accepts a value',
        'boolean_values' => 'must be one of: :true, :false',
    ],
    'rule_template' => 'The :label field :description.',

    'validate_progress' => 'Validating from :from to :to...',
    'import_progress' => 'Importing from :from to :to...',
    'validate_complete' => 'Validation complete. :total rows checked.',
    'import_complete' => 'Import complete. :imported imported, :failures failed.',
];
