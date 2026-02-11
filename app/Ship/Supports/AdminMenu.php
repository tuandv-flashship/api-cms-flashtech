<?php

namespace App\Ship\Supports;

use Illuminate\Contracts\Auth\Authenticatable;

final class AdminMenu
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function forUser(Authenticatable $user): array
    {
        $menu = config('admin-menu', []);

        if (! is_array($menu)) {
            return [];
        }

        return $this->filterNodes($menu, $user);
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @return array<int, array<string, mixed>>
     */
    private function filterNodes(array $nodes, Authenticatable $user): array
    {
        $filtered = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $hasChildrenConfig = isset($node['children']) && is_array($node['children']);
            $children = [];
            if ($hasChildrenConfig) {
                $children = $this->filterNodes($node['children'], $user);
            }

            $allowed = $this->isAllowed($node['permissions'] ?? [], $user);

            // Hide parent nodes when all children are filtered out.
            if ($hasChildrenConfig && $children === []) {
                continue;
            }

            if (! $allowed && $children === []) {
                continue;
            }

            if ($children !== []) {
                $node['children'] = $children;
            } else {
                unset($node['children']);
            }

            if (isset($node['name']) && is_string($node['name'])) {
                $node['title'] = __($node['name']);
            }

            $filtered[] = $node;
        }

        usort($filtered, static function (array $left, array $right): int {
            return ((int) ($left['priority'] ?? 0)) <=> ((int) ($right['priority'] ?? 0));
        });

        return $filtered;
    }

    /**
     * @param array<int, string>|string|null $permissions
     */
    private function isAllowed(array|string|null $permissions, Authenticatable $user): bool
    {
        if ($permissions === null || $permissions === []) {
            return true;
        }

        if (is_string($permissions)) {
            return method_exists($user, 'can') ? (bool) $user->can($permissions) : false;
        }

        if (! method_exists($user, 'canAny')) {
            return false;
        }

        return (bool) $user->canAny($permissions);
    }
}
