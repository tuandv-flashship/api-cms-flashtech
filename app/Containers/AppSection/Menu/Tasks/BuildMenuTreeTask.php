<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Collection;

final class BuildMenuTreeTask extends ParentTask
{
    /**
     * @param Collection<int, MenuNode> $nodes
     * @return Collection<int, MenuNode>
     */
    public function run(Collection $nodes): Collection
    {
        if ($nodes->isEmpty()) {
            return collect();
        }

        /** @var array<int, array<int, MenuNode>> $childrenByParent */
        $childrenByParent = [];
        /** @var array<int, MenuNode> $nodesById */
        $nodesById = [];

        foreach ($nodes as $node) {
            $nodeId = (int) $node->getKey();
            $parentId = (int) ($node->parent_id ?? 0);

            $nodesById[$nodeId] = $node;
            $childrenByParent[$parentId][] = $node;
        }

        foreach ($childrenByParent as &$branch) {
            if (count($branch) <= 1) {
                continue;
            }

            usort(
                $branch,
                static function (MenuNode $left, MenuNode $right): int {
                    $positionCompare = (int) $left->position <=> (int) $right->position;
                    if ($positionCompare !== 0) {
                        return $positionCompare;
                    }

                    return (int) $left->getKey() <=> (int) $right->getKey();
                },
            );
        }
        unset($branch);

        foreach ($nodesById as $nodeId => $node) {
            $children = $childrenByParent[$nodeId] ?? null;

            if ($children === null) {
                $node->setAttribute('has_child', false);
                continue;
            }

            $node->setRelation('children', collect($children));
            $node->setAttribute('has_child', true);
        }

        return collect($childrenByParent[0] ?? []);
    }
}
