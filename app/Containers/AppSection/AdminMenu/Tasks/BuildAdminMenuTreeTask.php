<?php

namespace App\Containers\AppSection\AdminMenu\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

final class BuildAdminMenuTreeTask extends ParentTask
{
    /**
     * Build nested tree from a flat collection in O(n) using hash-map.
     *
     * @return array<int, array<string, mixed>>
     */
    public function run(Collection $items): array
    {
        $indexed = [];
        foreach ($items as $item) {
            $indexed[$item->id] = $this->toNode($item);
        }

        $roots = [];
        foreach ($items as $item) {
            $node = &$indexed[$item->id];
            if ($item->parent_id !== null && isset($indexed[$item->parent_id])) {
                $indexed[$item->parent_id]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }
        unset($node);

        return $roots;
    }

    /**
     * @return array<string, mixed>
     */
    private function toNode(mixed $item): array
    {
        return [
            'id' => $item->getHashedKey(),
            'raw_id' => (int) $item->getKey(),
            'key' => $item->key,
            'name' => $item->name,
            'icon' => $item->icon,
            'route' => $item->route,
            'permissions' => $item->permissions,
            'children_display' => $item->children_display,
            'description' => $item->description,
            'priority' => $item->priority,
            'is_active' => $item->is_active,
            'children' => [],
        ];
    }
}
