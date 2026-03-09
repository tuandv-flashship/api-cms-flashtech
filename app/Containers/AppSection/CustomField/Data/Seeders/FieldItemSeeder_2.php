<?php

namespace App\Containers\AppSection\CustomField\Data\Seeders;

use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Containers\AppSection\CustomField\Models\FieldItemTranslation;
use App\Ship\Parents\Seeders\Seeder as ParentSeeder;

final class FieldItemSeeder_2 extends ParentSeeder
{
    public function run(): void
    {
        $fieldItems = [
            // Post Additional Information (field_group_id = 1)
            [
                'id' => 1,
                'field_group_id' => 1,
                'parent_id' => null,
                'order' => 0,
                'title' => 'Post Options',
                'slug' => 'post_options',
                'type' => 'checkbox',
                'instructions' => 'Select post display options',
                'options' => '{"selectChoices":"featured:Featured post\nsticky:Sticky post\nshow_author:Show author\nallow_comments:Allow comments\nshow_date:Show publish date"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Tuỳ chọn bài viết',
                        'instructions' => 'Chọn các tuỳ chọn hiển thị bài viết',
                        'options' => '{"selectChoices":"featured:Bài viết nổi bật\nsticky:Bài viết ghim\nshow_author:Hiển thị tác giả\nallow_comments:Cho phép bình luận\nshow_date:Hiển thị ngày đăng"}',
                    ],
                ],
            ],
            [
                'id' => 2,
                'field_group_id' => 1,
                'parent_id' => null,
                'order' => 1,
                'title' => 'Reading Time',
                'slug' => 'reading_time',
                'type' => 'number',
                'instructions' => 'Estimated reading time in minutes',
                'options' => '{"placeholderText":"5","defaultValue":"5","min":1,"max":60}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Thời gian đọc',
                        'instructions' => 'Thời gian đọc ước tính (phút)',
                        'options' => '{"placeholderText":"5","defaultValue":"5","min":1,"max":60}',
                    ],
                ],
            ],
            [
                'id' => 3,
                'field_group_id' => 1,
                'parent_id' => null,
                'order' => 2,
                'title' => 'External Source',
                'slug' => 'external_source',
                'type' => 'text',
                'instructions' => 'Link to external source or reference',
                'options' => '{"placeholderText":"https://example.com/article"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Nguồn bên ngoài',
                        'instructions' => 'Liên kết đến nguồn hoặc tài liệu tham khảo',
                        'options' => '{"placeholderText":"https://example.com/bai-viet"}',
                    ],
                ],
            ],
            [
                'id' => 4,
                'field_group_id' => 1,
                'parent_id' => null,
                'order' => 3,
                'title' => 'Post Type',
                'slug' => 'post_type',
                'type' => 'select',
                'instructions' => 'Select the type of post',
                'options' => '{"selectChoices":"article:Article\nnews:News\ntutorial:Tutorial\nreview:Review","defaultValue":"article"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Loại bài viết',
                        'instructions' => 'Chọn loại bài viết',
                        'options' => '{"selectChoices":"article:Bài viết\nnews:Tin tức\ntutorial:Hướng dẫn\nreview:Đánh giá","defaultValue":"article"}',
                    ],
                ],
            ],
            [
                'id' => 5,
                'field_group_id' => 1,
                'parent_id' => null,
                'order' => 4,
                'title' => 'Custom Excerpt',
                'slug' => 'custom_excerpt',
                'type' => 'textarea',
                'instructions' => 'Custom excerpt for social media sharing',
                'options' => '{"placeholderText":"Enter a brief summary...","rows":3}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Tóm tắt tuỳ chỉnh',
                        'instructions' => 'Tóm tắt tuỳ chỉnh cho chia sẻ mạng xã hội',
                        'options' => '{"placeholderText":"Nhập tóm tắt ngắn...","rows":3}',
                    ],
                ],
            ],
            [
                'id' => 6,
                'field_group_id' => 1,
                'parent_id' => null,
                'order' => 5,
                'title' => 'Sponsored By',
                'slug' => 'sponsored_by',
                'type' => 'text',
                'instructions' => 'Sponsor name (if applicable)',
                'options' => '{"placeholderText":"Company name"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Được tài trợ bởi',
                        'instructions' => 'Tên nhà tài trợ (nếu có)',
                        'options' => '{"placeholderText":"Tên công ty"}',
                    ],
                ],
            ],
            // Page Custom Fields (field_group_id = 2)
            [
                'id' => 7,
                'field_group_id' => 2,
                'parent_id' => null,
                'order' => 0,
                'title' => 'Hero Banner',
                'slug' => 'hero_banner',
                'type' => 'image',
                'instructions' => 'Upload a hero banner image for this page',
                'options' => '{"allow_thumb":true}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Ảnh banner',
                        'instructions' => 'Tải lên ảnh banner cho trang này',
                    ],
                ],
            ],
            [
                'id' => 8,
                'field_group_id' => 2,
                'parent_id' => null,
                'order' => 1,
                'title' => 'Page Subtitle',
                'slug' => 'page_subtitle',
                'type' => 'text',
                'instructions' => 'Add a subtitle or tagline for this page',
                'options' => '{"placeholderText":"Enter page subtitle"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Phụ đề trang',
                        'instructions' => 'Thêm phụ đề hoặc slogan cho trang',
                        'options' => '{"placeholderText":"Nhập phụ đề trang"}',
                    ],
                ],
            ],
            [
                'id' => 9,
                'field_group_id' => 2,
                'parent_id' => null,
                'order' => 2,
                'title' => 'Call to Action',
                'slug' => 'cta_button',
                'type' => 'text',
                'instructions' => 'Call to action button text',
                'options' => '{"placeholderText":"Learn More"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Nút kêu gọi hành động',
                        'instructions' => 'Nội dung nút kêu gọi hành động',
                        'options' => '{"placeholderText":"Tìm hiểu thêm"}',
                    ],
                ],
            ],
            [
                'id' => 10,
                'field_group_id' => 2,
                'parent_id' => null,
                'order' => 3,
                'title' => 'CTA Link',
                'slug' => 'cta_link',
                'type' => 'text',
                'instructions' => 'URL for the call to action button',
                'options' => '{"placeholderText":"https://example.com/contact"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Liên kết CTA',
                        'instructions' => 'URL cho nút kêu gọi hành động',
                        'options' => '{"placeholderText":"https://example.com/lien-he"}',
                    ],
                ],
            ],
            [
                'id' => 11,
                'field_group_id' => 2,
                'parent_id' => null,
                'order' => 4,
                'title' => 'Page Layout',
                'slug' => 'page_layout',
                'type' => 'radio',
                'instructions' => 'Select the page layout',
                'options' => '{"selectChoices":"default:Default Layout\nsidebar-left:Left Sidebar\nsidebar-right:Right Sidebar\nfull-width:Full Width","defaultValue":"default"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Bố cục trang',
                        'instructions' => 'Chọn bố cục trang',
                        'options' => '{"selectChoices":"default:Bố cục mặc định\nsidebar-left:Thanh bên trái\nsidebar-right:Thanh bên phải\nfull-width:Toàn chiều rộng","defaultValue":"default"}',
                    ],
                ],
            ],
            [
                'id' => 12,
                'field_group_id' => 2,
                'parent_id' => null,
                'order' => 5,
                'title' => 'Page Settings',
                'slug' => 'page_settings',
                'type' => 'checkbox',
                'instructions' => 'Select page display options',
                'options' => '{"selectChoices":"hide_title:Hide page title\nhide_breadcrumb:Hide breadcrumb\nhide_sidebar:Hide sidebar\nhide_footer:Hide footer"}',
                '_translations' => [
                    'vi' => [
                        'title' => 'Cài đặt trang',
                        'instructions' => 'Chọn các tuỳ chọn hiển thị trang',
                        'options' => '{"selectChoices":"hide_title:Ẩn tiêu đề trang\nhide_breadcrumb:Ẩn breadcrumb\nhide_sidebar:Ẩn thanh bên\nhide_footer:Ẩn chân trang"}',
                    ],
                ],
            ],
        ];

        foreach ($fieldItems as $itemData) {
            $translations = $itemData['_translations'] ?? [];
            unset($itemData['_translations']);

            $item = FieldItem::query()->firstOrCreate(
                ['id' => $itemData['id']],
                $itemData
            );

            foreach ($translations as $langCode => $fields) {
                \DB::table('field_items_translations')->upsert(
                    array_merge($fields, [
                        'lang_code' => $langCode,
                        'field_items_id' => $item->getKey(),
                    ]),
                    ['lang_code', 'field_items_id'],
                    array_keys($fields),
                );
            }
        }
    }
}
