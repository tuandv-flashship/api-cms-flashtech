<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ImportFieldGroupAction extends ParentAction
{
    /**
     * Import a FieldGroup from a portable JSON structure.
     * Supports optional translations key for auto-importing translations.
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

        // Import FieldGroup translations if present
        if (! empty($data['translations']) && is_array($data['translations'])) {
            $this->importGroupTranslations($data['translations'], (int) $group->getKey());
        }

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

            // Import FieldItem translations if present
            if (! empty($itemData['translations']) && is_array($itemData['translations'])) {
                $this->importItemTranslations($itemData['translations'], (int) $item->getKey());
            }

            if (! empty($itemData['items']) && is_array($itemData['items'])) {
                $this->importItems($itemData['items'], $groupId, (int) $item->getKey());
            }
        }
    }

    /**
     * Import translations for a FieldGroup.
     *
     * @param array<string, array<string, string>> $translations
     */
    private function importGroupTranslations(array $translations, int $groupId): void
    {
        foreach ($translations as $langCode => $fields) {
            if (! is_array($fields)) {
                continue;
            }

            DB::table('field_groups_translations')->upsert(
                array_merge($fields, [
                    'lang_code' => $langCode,
                    'field_groups_id' => $groupId,
                ]),
                ['lang_code', 'field_groups_id'],
                array_keys($fields),
            );
        }
    }

    /**
     * Import translations for a FieldItem.
     *
     * @param array<string, array<string, string>> $translations
     */
    private function importItemTranslations(array $translations, int $itemId): void
    {
        foreach ($translations as $langCode => $fields) {
            if (! is_array($fields)) {
                continue;
            }

            // Encode options if it's an array
            if (isset($fields['options']) && is_array($fields['options'])) {
                $fields['options'] = json_encode($fields['options']);
            }

            DB::table('field_items_translations')->upsert(
                array_merge($fields, [
                    'lang_code' => $langCode,
                    'field_items_id' => $itemId,
                ]),
                ['lang_code', 'field_items_id'],
                array_keys($fields),
            );
        }
    }
}
