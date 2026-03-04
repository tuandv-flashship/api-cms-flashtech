<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Tasks\BuildAdminMenuTreeTask;
use App\Containers\AppSection\AdminMenu\Tasks\ListAdminMenuItemsTask;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\BulkSaveAdminMenuItemsRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Containers\AppSection\AdminMenu\Supports\AdminMenu;
use Illuminate\Support\Facades\DB;

final class BulkSaveAdminMenuItemsAction extends ParentAction
{
    public function __construct(
        private readonly ListAdminMenuItemsTask $listTask,
        private readonly BuildAdminMenuTreeTask $buildTreeTask,
        private readonly AdminMenu $adminMenu,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(BulkSaveAdminMenuItemsRequest $request): array
    {
        $nodes = (array) $request->validated('items');
        $removeMissing = (bool) $request->validated('remove_missing', false);

        DB::transaction(function () use ($nodes, $removeMissing): void {
            if ($removeMissing) {
                // Full sync: soft-delete items not in the payload.
                $incomingKeys = $this->collectKeys($nodes);

                AdminMenuItem::query()
                    ->whereNotIn('key', $incomingKeys)
                    ->each(fn (AdminMenuItem $item) => $item->delete());
            }

            // Upsert items recursively.
            $this->syncNodes($nodes, null);
        });

        $this->adminMenu->flush();

        $items = $this->listTask->run(activeOnly: false);

        return $this->buildTreeTask->run($items);
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @return array<int, string>
     */
    private function collectKeys(array $nodes): array
    {
        $keys = [];
        foreach ($nodes as $node) {
            if (isset($node['key'])) {
                $keys[] = $node['key'];
            }
            if (isset($node['children']) && is_array($node['children'])) {
                $keys = array_merge($keys, $this->collectKeys($node['children']));
            }
        }

        return $keys;
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     */
    private function syncNodes(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $index => $nodeData) {
            $children = $nodeData['children'] ?? [];
            unset($nodeData['children']);

            $nodeData['parent_id'] = $parentId;
            $nodeData['priority'] = $nodeData['priority'] ?? ($index * 10);

            /** @var AdminMenuItem $item */
            $item = AdminMenuItem::withTrashed()->updateOrCreate(
                ['key' => $nodeData['key']],
                $nodeData,
            );

            // Restore if it was soft-deleted.
            if ($item->trashed()) {
                $item->restore();
            }

            if (is_array($children) && $children !== []) {
                $this->syncNodes($children, (int) $item->getKey());
            }
        }
    }
}
