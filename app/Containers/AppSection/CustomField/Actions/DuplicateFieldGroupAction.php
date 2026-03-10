<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DuplicateFieldGroupAction extends ParentAction
{
    /**
     * Deep clone a FieldGroup with all its FieldItems (preserving tree structure + translations).
     */
    public function run(int $id): FieldGroup
    {
        $source = FieldGroup::query()->findOrFail($id);

        $clone = $source->replicate();
        $clone->title = $source->getRawOriginal('title') . ' (Copy)';
        $clone->created_by = auth()->id();
        $clone->updated_by = auth()->id();
        $clone->save();

        // Clone FieldGroup translations
        $this->cloneGroupTranslations((int) $source->getKey(), (int) $clone->getKey());

        $this->duplicateItems(
            (int) $source->getKey(),
            (int) $clone->getKey(),
            null,
            null
        );

        return $clone->refresh();
    }

    /**
     * Recursively duplicate FieldItems, preserving parent-child relationships + translations.
     */
    private function duplicateItems(int $sourceGroupId, int $targetGroupId, ?int $sourceParentId, ?int $targetParentId): void
    {
        $items = FieldItem::query()
            ->where('field_group_id', $sourceGroupId)
            ->where('parent_id', $sourceParentId)
            ->orderBy('order')
            ->get();

        foreach ($items as $item) {
            $clone = $item->replicate();
            $clone->field_group_id = $targetGroupId;
            $clone->parent_id = $targetParentId;
            $clone->save();

            // Clone FieldItem translations
            $this->cloneItemTranslations((int) $item->getKey(), (int) $clone->getKey());

            // Recurse for children
            $this->duplicateItems(
                $sourceGroupId,
                $targetGroupId,
                (int) $item->getKey(),
                (int) $clone->getKey()
            );
        }
    }

    /**
     * Clone translations for a FieldGroup.
     */
    private function cloneGroupTranslations(int $sourceId, int $targetId): void
    {
        $rows = DB::table('field_groups_translations')
            ->where('field_groups_id', $sourceId)
            ->get();

        foreach ($rows as $row) {
            DB::table('field_groups_translations')->insert([
                'lang_code' => $row->lang_code,
                'field_groups_id' => $targetId,
                'title' => $row->title,
            ]);
        }
    }

    /**
     * Clone translations for a FieldItem.
     */
    private function cloneItemTranslations(int $sourceId, int $targetId): void
    {
        $rows = DB::table('field_items_translations')
            ->where('field_items_id', $sourceId)
            ->get();

        foreach ($rows as $row) {
            DB::table('field_items_translations')->insert([
                'lang_code' => $row->lang_code,
                'field_items_id' => $targetId,
                'title' => $row->title,
                'instructions' => $row->instructions,
                'options' => $row->options,
            ]);
        }
    }
}
