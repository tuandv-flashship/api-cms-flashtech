<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Str;

final class ImportFieldGroupAction extends ParentAction
{
    /**
     * Import a FieldGroup from a portable JSON structure.
     *
     * @param array<string, mixed> $data
     */
    public function run(array $data): FieldGroup
    {
        $group = FieldGroup::query()->create([
            'title' => $data['title'] ?? 'Imported Group',
            'rules' => isset($data['rules']) ? json_encode($data['rules']) : null,
            'order' => $data['order'] ?? 0,
            'status' => $data['status'] ?? 'published',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        if (! empty($data['items']) && is_array($data['items'])) {
            $this->importItems($data['items'], (int) $group->getKey(), null);
        }

        return $group->refresh();
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function importItems(array $items, int $groupId, ?int $parentId): void
    {
        foreach ($items as $position => $itemData) {
            if (! is_array($itemData)) {
                continue;
            }

            $slug = $itemData['slug'] ?? Str::slug($itemData['title'] ?? 'field');
            $options = isset($itemData['options']) && $itemData['options'] !== null
                ? (is_string($itemData['options']) ? $itemData['options'] : json_encode($itemData['options']))
                : null;

            $item = FieldItem::query()->create([
                'field_group_id' => $groupId,
                'parent_id' => $parentId,
                'order' => $itemData['order'] ?? $position,
                'title' => $itemData['title'] ?? 'Field',
                'slug' => $slug,
                'type' => $itemData['type'] ?? 'text',
                'instructions' => $itemData['instructions'] ?? null,
                'options' => $options,
            ]);

            if (! empty($itemData['items']) && is_array($itemData['items'])) {
                $this->importItems($itemData['items'], $groupId, (int) $item->getKey());
            }
        }
    }
}
