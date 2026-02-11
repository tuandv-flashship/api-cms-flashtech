<?php

namespace App\Containers\AppSection\Menu\Tests\Unit\Tasks;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Tasks\BuildMenuTreeTask;
use App\Containers\AppSection\Menu\Tests\UnitTestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BuildMenuTreeTask::class)]
final class BuildMenuTreeTaskPerformanceContractTest extends UnitTestCase
{
    public function testOptimizedImplementationKeepsTreeShapeCompatibleWithLegacy(): void
    {
        $sourceNodes = $this->generateNodes(1200, 20260211, true);
        $optimizedTask = app(BuildMenuTreeTask::class);

        $legacyTree = $this->runLegacy($this->cloneNodes($sourceNodes));
        $optimizedTree = $optimizedTask->run($this->cloneNodes($sourceNodes));

        $this->assertSame(
            $this->snapshotTree($legacyTree),
            $this->snapshotTree($optimizedTree),
        );
    }

    public function testOptimizedImplementationIsNotSlowerThanLegacyBaselineByMoreThanTwentyFivePercent(): void
    {
        $sourceNodes = $this->generateNodes(2500, 20260212, false);
        $optimizedTask = app(BuildMenuTreeTask::class);
        $iterations = 6;

        $legacyAverageMs = $this->averageExecutionMs(
            fn (Collection $nodes): Collection => $this->runLegacy($nodes),
            $sourceNodes,
            $iterations,
        );
        $optimizedAverageMs = $this->averageExecutionMs(
            static fn (Collection $nodes): Collection => $optimizedTask->run($nodes),
            $sourceNodes,
            $iterations,
        );

        $this->assertLessThanOrEqual(
            $legacyAverageMs * 1.25,
            $optimizedAverageMs,
            sprintf(
                'BuildMenuTreeTask regressed. legacy=%.3fms optimized=%.3fms',
                $legacyAverageMs,
                $optimizedAverageMs,
            ),
        );
    }

    /**
     * @return Collection<int, MenuNode>
     */
    private function generateNodes(int $size, int $seed, bool $uniquePosition): Collection
    {
        mt_srand($seed + $size);
        $nodes = [];
        $rootCount = max(1, intdiv($size, 40));

        for ($id = 1; $id <= $size; $id++) {
            $parentId = $id <= $rootCount ? null : mt_rand(1, $id - 1);

            $node = new MenuNode();
            $node->setAttribute('id', $id);
            $node->setAttribute('menu_id', 1);
            $node->setAttribute('parent_id', $parentId);
            $node->setAttribute('position', $uniquePosition ? $id : mt_rand(0, 50));
            $nodes[] = $node;
        }

        shuffle($nodes);

        return collect($nodes);
    }

    /**
     * @param Collection<int, MenuNode> $nodes
     * @return Collection<int, MenuNode>
     */
    private function cloneNodes(Collection $nodes): Collection
    {
        return $nodes->map(static function (MenuNode $node): MenuNode {
            $clone = clone $node;
            $clone->setRelations([]);

            return $clone;
        });
    }

    /**
     * @param Collection<int, MenuNode> $nodes
     * @return Collection<int, MenuNode>
     */
    private function runLegacy(Collection $nodes): Collection
    {
        $grouped = $nodes
            ->sortBy('position')
            ->groupBy(static fn (MenuNode $node): int => (int) ($node->parent_id ?? 0));

        return $this->buildLegacyBranch($grouped, 0);
    }

    /**
     * @param Collection<int, Collection<int, MenuNode>> $grouped
     * @return Collection<int, MenuNode>
     */
    private function buildLegacyBranch(Collection $grouped, int $parentId): Collection
    {
        /** @var Collection<int, MenuNode> $branch */
        $branch = $grouped->get($parentId, collect())->values();

        foreach ($branch as $node) {
            $children = $this->buildLegacyBranch($grouped, (int) $node->getKey());
            $node->setRelation('children', $children);
            $node->setAttribute('has_child', $children->isNotEmpty());
        }

        return $branch;
    }

    /**
     * @param callable(Collection<int, MenuNode>): Collection<int, MenuNode> $executor
     * @param Collection<int, MenuNode> $sourceNodes
     */
    private function averageExecutionMs(callable $executor, Collection $sourceNodes, int $iterations): float
    {
        // Warm up once to reduce cold-start noise.
        $executor($this->cloneNodes($sourceNodes));

        $elapsedNs = 0;

        for ($i = 0; $i < $iterations; $i++) {
            $nodes = $this->cloneNodes($sourceNodes);
            $startedAt = hrtime(true);
            $executor($nodes);
            $elapsedNs += hrtime(true) - $startedAt;
        }

        return ($elapsedNs / $iterations) / 1_000_000;
    }

    /**
     * @param Collection<int, MenuNode> $tree
     * @return array<int, string>
     */
    private function snapshotTree(Collection $tree): array
    {
        $snapshot = [];
        $walk = function (Collection $branch, int $depth) use (&$walk, &$snapshot): void {
            foreach ($branch as $index => $node) {
                $id = (int) $node->getKey();
                $parentId = (int) ($node->parent_id ?? 0);
                $hasChild = (bool) $node->has_child;
                $snapshot[] = sprintf(
                    '%d|%d|%d|%d|%d',
                    $id,
                    $parentId,
                    (int) $node->position,
                    $depth,
                    $index,
                );
                $snapshot[] = sprintf('hc|%d|%d', $id, $hasChild ? 1 : 0);

                /** @var Collection<int, MenuNode> $children */
                $children = $node->relationLoaded('children')
                    ? $node->getRelation('children')
                    : collect();
                $walk($children, $depth + 1);
            }
        };

        $walk($tree, 0);

        return $snapshot;
    }
}
