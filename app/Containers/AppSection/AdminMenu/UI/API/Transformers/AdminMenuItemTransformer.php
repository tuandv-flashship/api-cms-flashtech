<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Transformers;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use League\Fractal\TransformerAbstract;

final class AdminMenuItemTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    /**
     * @param AdminMenuItem|array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function transform(AdminMenuItem|array $item): array
    {
        // Support both Eloquent model and plain array (from tree builder).
        if ($item instanceof AdminMenuItem) {
            return $this->transformModel($item);
        }

        return $this->transformArray($item);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformModel(AdminMenuItem $item): array
    {
        $translatedName = $item->name;

        $data = [
            'id' => $item->getHashedKey(),
            'key' => $item->key,
            'name' => $item->getRawOriginal('name'),
            'title' => $translatedName ?? $item->getRawOriginal('name'),
            'icon' => $item->icon,
            'route' => $item->route,
            'permissions' => $item->permissions,
            'children_display' => $item->children_display,
            'description' => $item->description,
            'priority' => $item->priority,
            'is_active' => $item->is_active,
            'deleted_at' => $item->deleted_at?->toIso8601String(),
        ];

        if ($item->relationLoaded('children')) {
            $data['children'] = $item->children->map(
                fn (AdminMenuItem $child): array => $this->transformModel($child),
            )->all();
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function transformArray(array $item): array
    {
        $data = [
            'id' => $item['id'] ?? null,
            'key' => $item['key'] ?? null,
            'name' => $item['name'] ?? null,
            'title' => $item['title'] ?? $item['name'] ?? null,
            'icon' => $item['icon'] ?? null,
            'route' => $item['route'] ?? null,
            'permissions' => $item['permissions'] ?? null,
            'children_display' => $item['children_display'] ?? 'sidebar',
            'description' => $item['description'] ?? null,
            'priority' => $item['priority'] ?? 0,
            'is_active' => $item['is_active'] ?? true,
        ];

        if (isset($item['children']) && is_array($item['children'])) {
            $data['children'] = array_map(
                fn (array $child): array => $this->transformArray($child),
                $item['children'],
            );
        }

        return $data;
    }
}
