<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Collection;
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
        $groupId = (int) $group->getKey();

        // Batch-load all items and translations in 3 queries total
        $allItems = FieldItem::query()
            ->where('field_group_id', $groupId)
            ->orderBy('order')
            ->get();

        $itemIds = $allItems->pluck('id')->all();

        $groupTranslations = $this->getGroupTranslations($groupId);
        $allItemTranslations = $this->batchGetItemTranslations($itemIds);

        $result = [
            'title' => $group->getRawOriginal('title'),
            'rules' => $this->decodeJson($group->getRawOriginal('rules')),
            'order' => $group->order,
            'status' => $group->status?->value ?? (string) $group->status,
            'items' => $this->buildItemTree($allItems, $allItemTranslations, null),
        ];

        if (! empty($groupTranslations)) {
            $result['translations'] = $groupTranslations;
        }

        return $result;
    }

    /**
     * Build item tree recursively from pre-loaded collections.
     *
     * @param Collection<int, FieldItem> $allItems
     * @param array<int, array<string, array<string, string>>> $allTranslations
     * @return array<int, array<string, mixed>>
     */
    private function buildItemTree(Collection $allItems, array $allTranslations, ?int $parentId): array
    {
        $result = [];

        foreach ($allItems->where('parent_id', $parentId) as $item) {
            $itemId = (int) $item->getKey();
            $itemData = [
                'title' => $item->getRawOriginal('title'),
                'slug' => $item->slug,
                'type' => $item->type,
                'instructions' => $item->getRawOriginal('instructions'),
                'options' => $this->decodeJson($item->getRawOriginal('options')),
                'order' => $item->order,
                'items' => $this->buildItemTree($allItems, $allTranslations, $itemId),
            ];

            if (isset($allTranslations[$itemId])) {
                $itemData['translations'] = $allTranslations[$itemId];
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
     * Batch-load all translations for multiple FieldItems in a single query.
     *
     * @param array<int, int> $itemIds
     * @return array<int, array<string, array<string, string>>>
     */
    private function batchGetItemTranslations(array $itemIds): array
    {
        if ($itemIds === []) {
            return [];
        }

        $rows = DB::table('field_items_translations')
            ->whereIn('field_items_id', $itemIds)
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
                $translations[(int) $row->field_items_id][$row->lang_code] = $data;
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
