<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ExportFieldGroupAction extends ParentAction
{
    /**
     * Export a FieldGroup + all FieldItems as a portable JSON structure.
     *
     * @return array<string, mixed>
     */
    public function run(int $id): array
    {
        $group = FieldGroup::query()->findOrFail($id);

        return [
            'title' => $group->title,
            'rules' => $this->decodeJson($group->rules),
            'order' => $group->order,
            'status' => $group->status?->value ?? (string) $group->status,
            'items' => $this->exportItems((int) $group->getKey(), null),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportItems(int $groupId, ?int $parentId): array
    {
        $items = FieldItem::query()
            ->where('field_group_id', $groupId)
            ->where('parent_id', $parentId)
            ->orderBy('order')
            ->get();

        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'title' => $item->title,
                'slug' => $item->slug,
                'type' => $item->type,
                'instructions' => $item->instructions,
                'options' => $this->decodeJson($item->options),
                'order' => $item->order,
                'items' => $this->exportItems($groupId, (int) $item->getKey()),
            ];
        }

        return $result;
    }

    private function decodeJson(?string $value): mixed
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
