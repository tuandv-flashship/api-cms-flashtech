<?php

return [
    'types' => [
        'posts' => [
            'label' => 'Bài viết',
            'export_description' => 'Xuất bài viết sang tệp CSV/Excel.',
            'import_description' => 'Nhập bài viết từ tệp CSV/Excel.',
        ],
        'pages' => [
            'label' => 'Trang',
            'export_description' => 'Xuất trang sang tệp CSV/Excel.',
            'import_description' => 'Nhập trang từ tệp CSV/Excel.',
        ],
        'post-translations' => [
            'label' => 'Bản dịch bài viết',
            'export_description' => 'Xuất bản dịch cho bài viết sang tệp CSV/Excel.',
            'import_description' => 'Nhập bản dịch cho bài viết từ tệp CSV/Excel.',
        ],
        'page-translations' => [
            'label' => 'Bản dịch trang',
            'export_description' => 'Xuất bản dịch cho trang sang tệp CSV/Excel.',
            'import_description' => 'Nhập bản dịch cho trang từ tệp CSV/Excel.',
        ],
        'other-translations' => [
            'label' => 'Bản dịch khác',
            'export_description' => 'Xuất dữ liệu bản dịch khác sang tệp CSV/Excel.',
            'import_description' => 'Nhập dữ liệu bản dịch khác từ tệp CSV/Excel.',
        ],
    ],
    'filters' => [
        'limit' => 'Giới hạn',
        'status' => 'Trạng thái',
        'is_featured' => 'Nổi bật',
        'category' => 'Danh mục',
        'start_date' => 'Ngày bắt đầu',
        'end_date' => 'Ngày kết thúc',
        'template' => 'Mẫu',
        'limit_placeholder' => 'Để trống để xuất tất cả',
    ],
    'columns' => [
        'name' => 'Tên',
        'slug' => 'Slug',
        'description' => 'Mô tả',
        'content' => 'Nội dung',
        'status' => 'Trạng thái',
        'image' => 'Hình ảnh',
        'template' => 'Mẫu',
        'views' => 'Lượt xem',
        'url' => 'URL',
        'categories' => 'Danh mục',
        'tags' => 'Thẻ',
        'is_featured' => 'Nổi bật',
        'format_type' => 'Loại định dạng',
        'id' => 'ID',
    ],
    'rules' => [
        'required' => 'là bắt buộc',
        'string_type' => 'phải là chuỗi',
        'integer_type' => 'phải là số nguyên',
        'max_chars' => 'có độ dài tối đa :max ký tự',
        'may_blank' => 'có thể để trống',
        'allowed_values' => 'phải là một trong các giá trị cho phép',
        'accepts_value' => 'chấp nhận giá trị',
        'boolean_values' => 'phải là một trong: :true, :false',
    ],
    'rule_template' => 'Trường :label :description.',
];
