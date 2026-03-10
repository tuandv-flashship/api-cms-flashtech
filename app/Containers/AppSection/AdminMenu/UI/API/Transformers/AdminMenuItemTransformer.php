<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Transformers;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use League\Fractal\TransformerAbstract;

final class AdminMenuItemTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function __construct(
        private readonly bool $includeTranslations = false,
    ) {
    }

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
            'section' => $item->section,
            'description' => $item->description,
            'priority' => $item->priority,
            'is_active' => $item->is_active,
            'deleted_at' => $item->deleted_at?->toIso8601String(),
        ];

        if ($item->relationLoaded('children')) {
            $children = $item->children->map(
                fn (AdminMenuItem $child): array => $this->transformModel($child),
            )->all();

            $data['children'] = $children;

            if ($item->children_display === 'panel') {
                $data['sections'] = $this->groupBySection($children);
            }
        }

        if ($this->includeTranslations && $item->relationLoaded('allTranslations')) {
            $data['translations'] = $this->buildTranslationsMap($item);
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
            'section' => $item['section'] ?? null,
            'description' => $item['description'] ?? null,
            'priority' => $item['priority'] ?? 0,
            'is_active' => $item['is_active'] ?? true,
        ];

        if (isset($item['children']) && is_array($item['children'])) {
            $children = array_map(
                fn (array $child): array => $this->transformArray($child),
                $item['children'],
            );

            $data['children'] = $children;

            if (($item['children_display'] ?? 'sidebar') === 'panel') {
                $data['sections'] = $this->groupBySection($children);
            }
        }

        if ($this->includeTranslations && isset($item['_translations'])) {
            $data['translations'] = $item['_translations'];
        }

        return $data;
    }

    /**
     * Group children by section name, preserving order.
     *
     * @param  array<int, array<string, mixed>> $children
     * @return array<int, array{name: string, items: array<int, array<string, mixed>>}>
     */
    private function groupBySection(array $children): array
    {
        $groups = [];
        $order = [];

        foreach ($children as $child) {
            $section = $child['section'] ?? 'General';

            if (!isset($groups[$section])) {
                $groups[$section] = [];
                $order[] = $section;
            }

            $groups[$section][] = $child;
        }

        return array_map(
            fn (string $name): array => ['name' => $name, 'items' => $groups[$name]],
            $order,
        );
    }

    /**
     * Build translations map keyed by lang_code.
     *
     * @return array<string, array{name: string|null, description: string|null, section: string|null}>
     */
    private function buildTranslationsMap(AdminMenuItem $item): array
    {
        $map = [];

        foreach ($item->allTranslations as $translation) {
            $map[$translation->lang_code] = [
                'name' => $translation->name,
                'description' => $translation->description,
                'section' => $translation->section,
            ];
        }

        return $map;
    }
}
