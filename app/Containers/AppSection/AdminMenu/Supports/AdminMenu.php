<?php

namespace App\Containers\AppSection\AdminMenu\Supports;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

final class AdminMenu
{
    private const CACHE_PREFIX = 'admin-menu';

    private const DEFAULT_TTL_SECONDS = 3600;

    /**
     * Get the filtered, cached admin menu tree for a specific user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forUser(Authenticatable $user): array
    {
        $ttl = $this->ttlSeconds();

        if ($ttl <= 0) {
            return $this->resolveMenu($user);
        }

        $locale = (string) app()->getLocale();
        $userId = (int) $user->getAuthIdentifier();
        $cacheKey = $this->buildKey($userId, $locale);

        return Cache::remember($cacheKey, $ttl, fn (): array => $this->resolveMenu($user));
    }

    /**
     * Clear cached menu for a specific user (all locales).
     */
    public function forgetForUser(int $userId): void
    {
        foreach ($this->availableLocales() as $locale) {
            Cache::forget($this->buildKey($userId, $locale));
        }
    }

    /**
     * Clear cached menu for multiple users (all locales).
     *
     * @param array<int, int> $userIds
     */
    public function forgetForUsers(array $userIds): void
    {
        foreach ($userIds as $userId) {
            $this->forgetForUser((int) $userId);
        }
    }

    /**
     * Invalidate all admin-menu cache entries by incrementing the version counter.
     * Safe under concurrent requests (atomic increment).
     */
    public function flush(): void
    {
        $key = $this->versionKey();

        Cache::add($key, 1);
        Cache::increment($key);
    }

    // -------------------------------------------------------------------------
    //  Data Loading
    // -------------------------------------------------------------------------

    /**
     * Load menu from DB → config fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadMenu(): array
    {
        if (! $this->tableExists()) {
            return $this->loadFromConfig();
        }

        $with = LanguageAdvancedManager::withTranslations([], AdminMenuItem::class);

        $items = AdminMenuItem::query()
            ->where('is_active', true)
            ->with($with)
            ->orderBy('priority')
            ->get();

        if ($items->isEmpty()) {
            return $this->loadFromConfig();
        }

        return $this->buildTree($items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadFromConfig(): array
    {
        return config('admin-menu', []) ?: [];
    }

    /**
     * Load → filter by user permissions.
     *
     * @return array<int, array<string, mixed>>
     */
    private function resolveMenu(Authenticatable $user): array
    {
        $menu = $this->loadMenu();

        return $menu === [] ? [] : $this->filterNodes($menu, $user);
    }

    // -------------------------------------------------------------------------
    //  Tree Building (O(n) hash-map)
    // -------------------------------------------------------------------------

    /**
     * Build nested tree from flat Eloquent collection.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(Collection $items): array
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
    private function toNode(AdminMenuItem $item): array
    {
        // Translated values via HasLanguageTranslations accessor.
        $translatedName = $item->name;
        $translatedDesc = $item->description;
        $translatedSection = $item->section;

        return [
            'id' => $item->getHashedKey(),
            'key' => $item->key,
            'name' => $item->getRawOriginal('name'),
            'title' => $translatedName ?? $item->getRawOriginal('name'),
            'icon' => $item->icon,
            'route' => $item->route,
            'permissions' => $item->permissions,
            'children_display' => $item->children_display,
            'section' => $translatedSection ?? $item->getRawOriginal('section'),
            'description' => $translatedDesc ?? $item->getRawOriginal('description'),
            'priority' => $item->priority,
            'is_active' => $item->is_active,
        ];
    }

    // -------------------------------------------------------------------------
    //  Permission Filtering
    // -------------------------------------------------------------------------

    /**
     * Recursively filter nodes by user permissions, then sort by priority.
     *
     * @param  array<int, array<string, mixed>> $nodes
     * @return array<int, array<string, mixed>>
     */
    private function filterNodes(array $nodes, Authenticatable $user): array
    {
        $filtered = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $hasChildren = isset($node['children']) && is_array($node['children']);
            $children = $hasChildren ? $this->filterNodes($node['children'], $user) : [];
            $allowed = $this->isAllowed($node['permissions'] ?? [], $user);

            // Parent with no remaining children → hide.
            if ($hasChildren && $children === []) {
                continue;
            }

            // Leaf with no permission → hide.
            if (! $allowed && $children === []) {
                continue;
            }

            // Attach filtered children or remove empty key.
            if ($children !== []) {
                $node['children'] = $children;

                // Group children by section for panel-display parents.
                if (($node['children_display'] ?? 'sidebar') === 'panel') {
                    $node['sections'] = $this->groupBySection($children);
                }
            } else {
                unset($node['children']);
            }

            // Translate title for config-based items (DB items already have 'title' set by toNode).
            if (! isset($node['title'])) {
                $translationKey = $node['key'] ?? null;
                $name = $node['name'] ?? null;

                if ($translationKey !== null && is_string($translationKey)) {
                    $translated = __($translationKey);
                    // __() returns the key itself if no translation found — fall back to name.
                    $node['title'] = ($translated !== $translationKey) ? $translated : ($name ?? $translationKey);
                } elseif ($name !== null && is_string($name)) {
                    $node['title'] = $name;
                }
            }

            if (isset($node['description']) && is_string($node['description'])) {
                $translated = __($node['description']);
                if ($translated !== $node['description']) {
                    $node['description'] = $translated;
                }
            }

            $filtered[] = $node;
        }

        usort($filtered, static fn (array $a, array $b): int => ((int) ($a['priority'] ?? 0)) <=> ((int) ($b['priority'] ?? 0)));

        return $filtered;
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
            static fn (string $name): array => ['name' => $name, 'items' => $groups[$name]],
            $order,
        );
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
            return method_exists($user, 'can') && (bool) $user->can($permissions);
        }

        return method_exists($user, 'canAny') && (bool) $user->canAny($permissions);
    }

    // -------------------------------------------------------------------------
    //  Cache Helpers
    // -------------------------------------------------------------------------

    private function buildKey(int $userId, string $locale): string
    {
        return sprintf('%s:v%d:u%d:%s', self::CACHE_PREFIX, $this->currentVersion(), $userId, $locale);
    }

    private function versionKey(): string
    {
        return self::CACHE_PREFIX . ':version';
    }

    private function currentVersion(): int
    {
        $version = (int) Cache::rememberForever($this->versionKey(), static fn (): int => 1);

        return max($version, 1);
    }

    private function ttlSeconds(): int
    {
        return (int) config('admin-menu-container.cache_ttl_seconds', self::DEFAULT_TTL_SECONDS);
    }

    /**
     * @return array<int, string>
     */
    private function availableLocales(): array
    {
        $locales = config('app.available_locales');

        if (is_array($locales) && $locales !== []) {
            return $locales;
        }

        $langPath = lang_path();
        if (is_dir($langPath)) {
            $dirs = array_filter(
                scandir($langPath) ?: [],
                static fn (string $dir): bool => $dir !== '.' && $dir !== '..' && is_dir($langPath . '/' . $dir),
            );

            if ($dirs !== []) {
                return array_values($dirs);
            }
        }

        return [(string) app()->getLocale()];
    }

    // -------------------------------------------------------------------------
    //  Migration Safety
    // -------------------------------------------------------------------------

    private ?bool $tableExistsCache = null;

    private function tableExists(): bool
    {
        if ($this->tableExistsCache === null) {
            try {
                $this->tableExistsCache = Schema::hasTable('admin_menu_items');
            } catch (\Throwable) {
                $this->tableExistsCache = false;
            }
        }

        return $this->tableExistsCache;
    }
}
