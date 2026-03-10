<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DuplicateFieldGroupAction extends ParentAction
{
    /**
     * Deep clone a FieldGroup with all its FieldItems (preserving tree structure + translations).
     */
    public function run(int $id): FieldGroup
    {
        $source = FieldGroup::query()->findOrFail($id);
        $sourceId = (int) $source->getKey();

        // Clone group
        $clone = $source->replicate();
        $clone->title = $source->getRawOriginal('title') . ' (Copy)';
        $clone->created_by = auth()->id();
        $clone->updated_by = auth()->id();
        $clone->save();
        $cloneId = (int) $clone->getKey();

        // Clone group translations (batch)
        $this->cloneGroupTranslations($sourceId, $cloneId);

        // Batch-load all items and translations
        $allItems = FieldItem::query()
            ->where('field_group_id', $sourceId)
            ->orderBy('order')
            ->get();

        $itemIds = $allItems->pluck('id')->all();
        $allItemTranslations = $this->batchLoadItemTranslations($itemIds);

        // Clone items recursively, collecting old→new ID mapping
        $idMap = [];
        $this->duplicateItems($allItems, $cloneId, null, $idMap);

        // Batch-insert item translations using the ID mapping
        $this->batchCloneItemTranslations($allItemTranslations, $idMap);

        return $clone->refresh();
    }

    /**
     * Recursively duplicate FieldItems, preserving parent-child relationships.
     * Populates $idMap with old_id => new_id mapping.
     *
     * @param Collection<int, FieldItem> $allItems
     * @param array<int, int> &$idMap
     */
    private function duplicateItems(Collection $allItems, int $targetGroupId, ?int $parentId, array &$idMap): void
    {
        foreach ($allItems->where('parent_id', $parentId) as $item) {
            $oldId = (int) $item->getKey();

            $clone = $item->replicate();
            $clone->field_group_id = $targetGroupId;
            $clone->parent_id = isset($idMap[$parentId]) ? $idMap[$parentId] : $parentId;
            $clone->save();

            $idMap[$oldId] = (int) $clone->getKey();

            // Recurse for children
            $this->duplicateItems($allItems, $targetGroupId, $oldId, $idMap);
        }
    }

    /**
     * Clone translations for a FieldGroup (single query).
     */
    private function cloneGroupTranslations(int $sourceId, int $targetId): void
    {
        $rows = DB::table('field_groups_translations')
            ->where('field_groups_id', $sourceId)
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $inserts = [];
        foreach ($rows as $row) {
            $inserts[] = [
                'lang_code' => $row->lang_code,
                'field_groups_id' => $targetId,
                'title' => $row->title,
            ];
        }

        DB::table('field_groups_translations')->insert($inserts);
    }

    /**
     * Batch-load all item translations.
     *
     * @param array<int, int> $itemIds
     * @return Collection<int, \stdClass>
     */
    private function batchLoadItemTranslations(array $itemIds): Collection
    {
        if ($itemIds === []) {
            return collect();
        }

        return DB::table('field_items_translations')
            ->whereIn('field_items_id', $itemIds)
            ->get();
    }

    /**
     * Batch-insert cloned item translations using old→new ID mapping.
     *
     * @param Collection<int, \stdClass> $translations
     * @param array<int, int> $idMap
     */
    private function batchCloneItemTranslations(Collection $translations, array $idMap): void
    {
        if ($translations->isEmpty()) {
            return;
        }

        $inserts = [];
        foreach ($translations as $row) {
            $newId = $idMap[(int) $row->field_items_id] ?? null;
            if ($newId === null) {
                continue;
            }

            $inserts[] = [
                'lang_code' => $row->lang_code,
                'field_items_id' => $newId,
                'title' => $row->title,
                'instructions' => $row->instructions,
                'options' => $row->options,
            ];
        }

        if ($inserts !== []) {
            DB::table('field_items_translations')->insert($inserts);
        }
    }
}
