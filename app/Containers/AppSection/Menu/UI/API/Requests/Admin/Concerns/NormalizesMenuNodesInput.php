<?php

namespace App\Containers\AppSection\Menu\UI\API\Requests\Admin\Concerns;

trait NormalizesMenuNodesInput
{
    /**
     * @param array<int, mixed> $nodes
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeNodes(array $nodes): array
    {
        $normalized = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            foreach (['id', 'parent_id', 'reference_id'] as $field) {
                if (isset($node[$field])) {
                    $node[$field] = $this->decodeToInteger($node[$field]);
                }
            }

            if (isset($node['reference_type']) && is_string($node['reference_type'])) {
                $node['reference_type'] = $this->normalizeReferenceType($node['reference_type']);
            }

            if (! array_key_exists('url_source', $node)) {
                $node['url_source'] = isset($node['reference_type'], $node['reference_id']) ? 'resolved' : 'custom';
            }

            if (! array_key_exists('title_source', $node)) {
                $node['title_source'] = isset($node['reference_type'], $node['reference_id']) ? 'resolved' : 'custom';
            }

            if (! array_key_exists('target', $node) || $node['target'] === null || $node['target'] === '') {
                $node['target'] = '_self';
            }

            $children = $node['children'] ?? [];
            $node['children'] = is_array($children) ? $this->normalizeNodes($children) : [];

            $normalized[] = $node;
        }

        return $normalized;
    }

    protected function decodeToInteger(mixed $value): mixed
    {
        if (is_int($value) || $value === null || $value === '') {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        try {
            $decoded = hashids()->decodeOrFail($value);

            return is_int($decoded) ? $decoded : $value;
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function normalizeReferenceType(string $referenceType): ?string
    {
        $referenceType = trim($referenceType);
        if ($referenceType === '') {
            return null;
        }

        $types = (array) config('menu.reference_types', []);

        if (isset($types[$referenceType])) {
            return (string) $types[$referenceType];
        }

        return in_array($referenceType, $types, true) ? $referenceType : null;
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @return array<int, array<string, mixed>>
     */
    protected function flattenNodes(array $nodes): array
    {
        $flat = [];
        $this->appendFlattenedNodes($nodes, $flat);

        return $flat;
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @param array<int, array<string, mixed>> $flat
     */
    private function appendFlattenedNodes(array $nodes, array &$flat): void
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $flat[] = $node;

            $children = $node['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $this->appendFlattenedNodes($children, $flat);
            }
        }
    }

    protected function hasNodeLoopInTree(array $nodes, array $ancestorIds = []): bool
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $nextAncestors = $ancestorIds;
            $id = $node['id'] ?? null;
            if (is_int($id)) {
                if (in_array($id, $ancestorIds, true)) {
                    return true;
                }

                $nextAncestors[] = $id;
            }

            $children = $node['children'] ?? [];
            if (is_array($children) && $children !== [] && $this->hasNodeLoopInTree($children, $nextAncestors)) {
                return true;
            }
        }

        return false;
    }

    protected function hasParentIdLoop(array $nodes): bool
    {
        $parentMap = [];

        foreach ($this->flattenNodes($nodes) as $node) {
            if (! is_array($node)) {
                continue;
            }

            $id = $node['id'] ?? null;
            $parentId = $node['parent_id'] ?? null;

            if (! is_int($id) || ! is_int($parentId)) {
                continue;
            }

            if ($id === $parentId) {
                return true;
            }

            $parentMap[$id] = $parentId;
        }

        foreach (array_keys($parentMap) as $nodeId) {
            $seen = [];
            $current = $nodeId;

            while (isset($parentMap[$current])) {
                if (isset($seen[$current])) {
                    return true;
                }

                $seen[$current] = true;
                $current = $parentMap[$current];
            }
        }

        return false;
    }
}
