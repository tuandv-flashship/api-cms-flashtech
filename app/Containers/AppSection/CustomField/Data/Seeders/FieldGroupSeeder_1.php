<?php

namespace App\Containers\AppSection\CustomField\Data\Seeders;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldGroupTranslation;
use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Parents\Seeders\Seeder as ParentSeeder;

final class FieldGroupSeeder_1 extends ParentSeeder
{
    public function run(): void
    {
        $fieldGroups = [
            [
                'id' => 1,
                'title' => 'Post Additional Information',
                'rules' => json_encode([
                    [
                        ['name' => 'model_name', 'type' => '==', 'value' => Post::class],
                    ],
                ]),
                'order' => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => ContentStatus::PUBLISHED,
                '_translations' => [
                    'vi' => ['title' => 'Thông tin bổ sung bài viết'],
                ],
            ],
            [
                'id' => 2,
                'title' => 'Page Custom Fields',
                'rules' => json_encode([
                    [
                        ['name' => 'model_name', 'type' => '==', 'value' => Page::class],
                    ],
                ]),
                'order' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => ContentStatus::PUBLISHED,
                '_translations' => [
                    'vi' => ['title' => 'Trường tuỳ chỉnh trang'],
                ],
            ],
        ];

        foreach ($fieldGroups as $groupData) {
            $translations = $groupData['_translations'] ?? [];
            unset($groupData['_translations']);

            $group = FieldGroup::query()->firstOrCreate(
                ['id' => $groupData['id']],
                $groupData
            );

            foreach ($translations as $langCode => $fields) {
                \DB::table('field_groups_translations')->upsert(
                    array_merge($fields, [
                        'lang_code' => $langCode,
                        'field_groups_id' => $group->getKey(),
                    ]),
                    ['lang_code', 'field_groups_id'],
                    array_keys($fields),
                );
            }
        }
    }
}
