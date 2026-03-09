<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DuplicateFieldGroupAction extends ParentAction
{
    /**
     * Deep clone a FieldGroup with all its FieldItems (preserving tree structure).
     */
    public function run(int $id): FieldGroup
    {
        $source = FieldGroup::query()->findOrFail($id);

        $clone = $source->replicate();
        $clone->title = $source->title . ' (Copy)';
        $clone->created_by = auth()->id();
        $clone->updated_by = auth()->id();
        $clone->save();

        $this->duplicateItems(
            (int) $source->getKey(),
            (int) $clone->getKey(),
            null,
            null
        );

        return $clone->refresh();
    }

    /**
     * Recursively duplicate FieldItems, preserving parent-child relationships.
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

            // Recurse for children
            $this->duplicateItems(
                $sourceGroupId,
                $targetGroupId,
                (int) $item->getKey(),
                (int) $clone->getKey()
            );
        }
    }
}
