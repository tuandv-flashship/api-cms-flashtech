<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class RefreshMenuNodeHasChildTask extends ParentTask
{
    /**
     * @param array<int, int> $menuIds
     */
    public function runForMenuIds(array $menuIds): void
    {
        $menuIds = collect($menuIds)
            ->map(static fn (mixed $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($menuIds === []) {
            return;
        }

        MenuNode::query()
            ->whereIn('menu_id', $menuIds)
            ->update(['has_child' => false]);

        $parentIds = MenuNode::query()
            ->whereIn('menu_id', $menuIds)
            ->whereNotNull('parent_id')
            ->distinct()
            ->pluck('parent_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($parentIds !== []) {
            MenuNode::query()->whereIn('id', $parentIds)->update(['has_child' => true]);
        }
    }

    public function runForMenuId(int $menuId): void
    {
        $this->runForMenuIds([$menuId]);
    }
}
