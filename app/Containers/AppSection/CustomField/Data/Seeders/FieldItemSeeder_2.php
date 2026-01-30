<?php

namespace App\Containers\AppSection\CustomField\Data\Seeders;

use App\Containers\AppSection\CustomField\Models\FieldItem;
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
            ],
        ];

        foreach ($fieldItems as $item) {
            FieldItem::query()->firstOrCreate(
                ['id' => $item['id']],
                $item
            );
        }
    }
}
