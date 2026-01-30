<?php

namespace App\Containers\AppSection\CustomField\Data\Seeders;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\CustomField\Models\FieldGroup;
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
                // Rules format: array of rule groups, each group is array of rules
                // [[{rule1}, {rule2}], [{rule3}]] means: (rule1 AND rule2) OR (rule3)
                'rules' => json_encode([
                    [
                        ['name' => 'model_name', 'type' => '==', 'value' => Post::class],
                    ],
                ]),
                'order' => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => ContentStatus::PUBLISHED,
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
            ],
        ];

        foreach ($fieldGroups as $group) {
            FieldGroup::query()->firstOrCreate(
                ['id' => $group['id']],
                $group
            );
        }
    }
}


