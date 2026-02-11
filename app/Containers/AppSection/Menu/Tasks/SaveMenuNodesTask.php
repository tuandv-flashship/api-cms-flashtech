<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Supports\MenuNodeResolver;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Collection;

final class SaveMenuNodesTask extends ParentTask
{
    public function __construct(
        private readonly MenuNodeResolver $resolver,
        private readonly RefreshMenuNodeHasChildTask $refreshMenuNodeHasChildTask,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     */
    public function run(int $menuId, array $nodes): void
    {
        /** @var Collection<int, MenuNode> $existingNodes */
        $existingNodes = MenuNode::query()
            ->where('menu_id', $menuId)
            ->get()
            ->keyBy('id');

        $resolvedReferences = $this->resolver->resolveMany($this->collectReferences($nodes));
        $keepIds = [];

        $this->persistBranch($menuId, $nodes, null, $keepIds, $existingNodes, $resolvedReferences);

        $this->deleteRemovedNodes($menuId, $keepIds, $existingNodes);

        $this->refreshMenuNodeHasChildTask->runForMenuId($menuId);
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @param array<int, int> $keepIds
     * @param Collection<int, MenuNode> $existingNodes
     * @param array<string, array{reference_type: string|null, title: string|null, url: string|null}> $resolvedReferences
     */
    private function persistBranch(
        int $menuId,
        array $nodes,
        ?int $parentId,
        array &$keepIds,
        Collection $existingNodes,
        array $resolvedReferences,
    ): void
    {
        foreach ($nodes as $position => $nodeData) {
            if (! is_array($nodeData)) {
                continue;
            }

            $savedNode = $this->saveSingleNode(
                $menuId,
                $parentId,
                (int) $position,
                $nodeData,
                $existingNodes,
                $resolvedReferences,
            );
            $keepIds[] = (int) $savedNode->getKey();
            $existingNodes->put((int) $savedNode->getKey(), $savedNode);

            $children = $nodeData['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $this->persistBranch(
                    $menuId,
                    $children,
                    (int) $savedNode->getKey(),
                    $keepIds,
                    $existingNodes,
                    $resolvedReferences,
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $nodeData
     * @param Collection<int, MenuNode> $existingNodes
     * @param array<string, array{reference_type: string|null, title: string|null, url: string|null}> $resolvedReferences
     */
    private function saveSingleNode(
        int $menuId,
        ?int $parentId,
        int $position,
        array $nodeData,
        Collection $existingNodes,
        array $resolvedReferences,
    ): MenuNode
    {
        $existingId = isset($nodeData['id']) ? (int) $nodeData['id'] : null;
        $node = $existingId ? $existingNodes->get($existingId) : null;

        $payload = [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'reference_type' => null,
            'reference_id' => null,
            'url' => isset($nodeData['url']) ? (string) $nodeData['url'] : null,
            'title' => isset($nodeData['title']) ? (string) $nodeData['title'] : null,
            'url_source' => $this->normalizeSource($nodeData['url_source'] ?? null),
            'title_source' => $this->normalizeSource($nodeData['title_source'] ?? null),
            'icon_font' => isset($nodeData['icon_font']) ? (string) $nodeData['icon_font'] : null,
            'css_class' => isset($nodeData['css_class']) ? (string) $nodeData['css_class'] : null,
            'target' => isset($nodeData['target']) ? (string) $nodeData['target'] : '_self',
            'has_child' => false,
            'position' => $position,
        ];

        $referenceKey = $this->resolver->buildKey(
            isset($nodeData['reference_type']) ? (string) $nodeData['reference_type'] : null,
            isset($nodeData['reference_id']) ? (int) $nodeData['reference_id'] : null,
        );
        $resolved = $resolvedReferences[$referenceKey] ?? [
            'reference_type' => null,
            'title' => null,
            'url' => null,
        ];

        $payload['reference_type'] = $resolved['reference_type'];
        $payload['reference_id'] = $resolved['reference_type'] !== null && isset($nodeData['reference_id'])
            ? (int) $nodeData['reference_id']
            : null;

        if ($payload['url_source'] === 'resolved' && $resolved['url'] !== null) {
            $payload['url'] = $resolved['url'];
        }

        if ($payload['title_source'] === 'resolved' && $resolved['title'] !== null) {
            $payload['title'] = $resolved['title'];
        }

        if ($node !== null) {
            $node->fill($payload);
            if ($node->isDirty()) {
                $node->save();
            }

            return $node;
        }

        return MenuNode::query()->create($payload);
    }

    private function normalizeSource(mixed $source): string
    {
        if (! is_string($source)) {
            return 'custom';
        }

        return in_array($source, ['custom', 'resolved'], true) ? $source : 'custom';
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @return array<int, array<string, mixed>>
     */
    private function collectReferences(array $nodes): array
    {
        $references = [];

        $append = function (array $branch) use (&$append, &$references): void {
            foreach ($branch as $nodeData) {
                if (! is_array($nodeData)) {
                    continue;
                }

                $references[] = [
                    'reference_type' => $nodeData['reference_type'] ?? null,
                    'reference_id' => $nodeData['reference_id'] ?? null,
                ];

                $children = $nodeData['children'] ?? [];
                if (is_array($children) && $children !== []) {
                    $append($children);
                }
            }
        };

        $append($nodes);

        return $references;
    }

    /**
     * @param array<int, int> $keepIds
     * @param Collection<int, MenuNode> $existingNodes
     */
    private function deleteRemovedNodes(int $menuId, array $keepIds, Collection $existingNodes): void
    {
        if ($keepIds === []) {
            MenuNode::query()->where('menu_id', $menuId)->delete();

            return;
        }

        $keepMap = array_fill_keys($keepIds, true);
        $deleteIds = [];

        foreach ($existingNodes as $node) {
            $nodeId = (int) $node->getKey();
            if (! isset($keepMap[$nodeId])) {
                $deleteIds[] = $nodeId;
            }
        }

        foreach (array_chunk($deleteIds, 500) as $chunk) {
            MenuNode::query()->whereIn('id', $chunk)->delete();
        }
    }
}
