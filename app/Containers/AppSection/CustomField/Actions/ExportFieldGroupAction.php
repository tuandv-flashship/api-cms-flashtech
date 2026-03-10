<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class ExportFieldGroupAction extends ParentAction
{
    /**
     * Export a FieldGroup + all FieldItems as a portable JSON structure.
     * Includes translations from all locales for portable backup/sharing.
     *
     * @return array<string, mixed>
     */
    public function run(int $id): array
    {
        $group = FieldGroup::query()->findOrFail($id);

        $groupTranslations = $this->getGroupTranslations((int) $group->getKey());

        $result = [
            'title' => $group->getRawOriginal('title'),
            'rules' => $this->decodeJson($group->getRawOriginal('rules')),
            'order' => $group->order,
            'status' => $group->status?->value ?? (string) $group->status,
            'items' => $this->exportItems((int) $group->getKey(), null),
        ];

        if (! empty($groupTranslations)) {
            $result['translations'] = $groupTranslations;
        }

        return $result;
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
            $itemData = [
                'title' => $item->getRawOriginal('title'),
                'slug' => $item->slug,
                'type' => $item->type,
                'instructions' => $item->getRawOriginal('instructions'),
                'options' => $this->decodeJson($item->getRawOriginal('options')),
                'order' => $item->order,
                'items' => $this->exportItems($groupId, (int) $item->getKey()),
            ];

            $itemTranslations = $this->getItemTranslations((int) $item->getKey());
            if (! empty($itemTranslations)) {
                $itemData['translations'] = $itemTranslations;
            }

            $result[] = $itemData;
        }

        return $result;
    }

    /**
     * Get translations for a FieldGroup, keyed by lang_code.
     *
     * @return array<string, array<string, string>>
     */
    private function getGroupTranslations(int $groupId): array
    {
        $rows = DB::table('field_groups_translations')
            ->where('field_groups_id', $groupId)
            ->get();

        $translations = [];
        foreach ($rows as $row) {
            $data = [];
            if ($row->title !== null) {
                $data['title'] = $row->title;
            }
            if (! empty($data)) {
                $translations[$row->lang_code] = $data;
            }
        }

        return $translations;
    }

    /**
     * Get translations for a FieldItem, keyed by lang_code.
     *
     * @return array<string, array<string, string>>
     */
    private function getItemTranslations(int $itemId): array
    {
        $rows = DB::table('field_items_translations')
            ->where('field_items_id', $itemId)
            ->get();

        $translations = [];
        foreach ($rows as $row) {
            $data = [];
            foreach (['title', 'instructions', 'options'] as $col) {
                if ($row->$col !== null) {
                    $data[$col] = $col === 'options'
                        ? ($this->decodeJson($row->$col) ?? $row->$col)
                        : $row->$col;
                }
            }
            if (! empty($data)) {
                $translations[$row->lang_code] = $data;
            }
        }

        return $translations;
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
