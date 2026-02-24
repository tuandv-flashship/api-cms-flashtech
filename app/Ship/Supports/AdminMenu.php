<?php

namespace App\Ship\Supports;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;

final class AdminMenu
{
    private const CACHE_PREFIX = 'admin-menu';

    private const DEFAULT_TTL_SECONDS = 3600;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forUser(Authenticatable $user): array
    {
        $menu = config('admin-menu', []);

        if (! is_array($menu)) {
            return [];
        }

        $locale = (string) app()->getLocale();
        $userId = (int) $user->getAuthIdentifier();
        $cacheKey = $this->buildKey($userId, $locale);
        $ttl = $this->ttlSeconds();

        if ($ttl <= 0) {
            return $this->filterNodes($menu, $user);
        }

        return Cache::remember($cacheKey, $ttl, fn (): array => $this->filterNodes($menu, $user));
    }

    /**
     * Clear cached menu for a specific user (all locales).
     */
    public function forgetForUser(int $userId): void
    {
        $locales = $this->availableLocales();

        foreach ($locales as $locale) {
            Cache::forget($this->buildKey($userId, $locale));
        }
    }

    /**
     * Clear cached menu for all given user IDs (all locales).
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
     * Clear all admin-menu cache entries by incrementing the version.
     */
    public function flush(): void
    {
        $key = $this->versionKey();

        // Ensure key exists (no-op if already present).
        Cache::add($key, 1);

        // Atomic increment — safe under concurrent requests (Redis, DB, etc.)
        Cache::increment($key);
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

            if (isset($node['description']) && is_string($node['description'])) {
                $node['description'] = __($node['description']);
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

    private function buildKey(int $userId, string $locale): string
    {
        $version = $this->currentVersion();

        return sprintf('%s:v%d:u%d:%s', self::CACHE_PREFIX, $version, $userId, $locale);
    }

    private function versionKey(): string
    {
        return self::CACHE_PREFIX . ':version';
    }

    private function currentVersion(): int
    {
        $version = (int) Cache::rememberForever($this->versionKey(), static fn (): int => 1);

        return $version > 0 ? $version : 1;
    }

    private function ttlSeconds(): int
    {
        return (int) config('admin-menu-cache.ttl_seconds', self::DEFAULT_TTL_SECONDS);
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

        // Fallback: scan lang directory
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
}
