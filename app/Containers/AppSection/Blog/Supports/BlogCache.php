<?php

namespace App\Containers\AppSection\Blog\Supports;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Blog caching support for frequently-accessed data.
 * TTL: 1 day (as safety net, events handle immediate invalidation).
 */
final class BlogCache
{
    private const CATEGORIES_ALL = 'blog.categories.all';
    private const CATEGORIES_TREE = 'blog.categories.tree';
    private const TAGS_ALL = 'blog.tags.all';
    private const CATEGORY_PREFIX = 'blog.category.';
    private const TAG_PREFIX = 'blog.tag.';
    private const TTL_SECONDS = 86400; // 1 day

    /**
     * Get all published categories (cached).
     */
    public static function getPublishedCategories(): Collection
    {
        return Cache::remember(self::CATEGORIES_ALL, self::TTL_SECONDS, function (): Collection {
            $with = LanguageAdvancedManager::withTranslations(['slugable', 'parent'], Category::class);

            return Category::query()
                ->with($with)
                ->where('status', 'published')
                ->orderBy('order')
                ->get();
        });
    }

    /**
     * Get categories as nested tree (cached).
     */
    public static function getCategoriesTree(): Collection
    {
        return Cache::remember(self::CATEGORIES_TREE, self::TTL_SECONDS, function (): Collection {
            $with = LanguageAdvancedManager::withTranslations(['slugable'], Category::class);

            $categories = Category::query()
                ->with($with)
                ->where('status', 'published')
                ->orderBy('order')
                ->get();

            return self::buildTree($categories);
        });
    }

    /**
     * Get all published tags (cached).
     */
    public static function getPublishedTags(): Collection
    {
        return Cache::remember(self::TAGS_ALL, self::TTL_SECONDS, function (): Collection {
            $with = LanguageAdvancedManager::withTranslations(['slugable'], Tag::class);

            return Tag::query()
                ->with($with)
                ->where('status', 'published')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get a single category by ID (cached).
     */
    public static function getCategory(int $id): ?Category
    {
        return Cache::remember(self::CATEGORY_PREFIX . $id, self::TTL_SECONDS, function () use ($id): ?Category {
            $with = LanguageAdvancedManager::withTranslations(['slugable', 'parent'], Category::class);

            return Category::query()->with($with)->find($id);
        });
    }

    /**
     * Get a single tag by ID (cached).
     */
    public static function getTag(int $id): ?Tag
    {
        return Cache::remember(self::TAG_PREFIX . $id, self::TTL_SECONDS, function () use ($id): ?Tag {
            $with = LanguageAdvancedManager::withTranslations(['slugable'], Tag::class);

            return Tag::query()->with($with)->find($id);
        });
    }

    /**
     * Forget all category caches.
     */
    public static function forgetCategories(): void
    {
        Cache::forget(self::CATEGORIES_ALL);
        Cache::forget(self::CATEGORIES_TREE);
    }

    /**
     * Forget a specific category cache.
     */
    public static function forgetCategory(int $id): void
    {
        self::forgetCategories();
        Cache::forget(self::CATEGORY_PREFIX . $id);
    }

    /**
     * Forget all tag caches.
     */
    public static function forgetTags(): void
    {
        Cache::forget(self::TAGS_ALL);
    }

    /**
     * Forget a specific tag cache.
     */
    public static function forgetTag(int $id): void
    {
        self::forgetTags();
        Cache::forget(self::TAG_PREFIX . $id);
    }

    /**
     * Forget all blog caches.
     */
    public static function forgetAll(): void
    {
        self::forgetCategories();
        self::forgetTags();
    }

    /**
     * Build nested tree from flat collection.
     */
    private static function buildTree(Collection $categories, int $parentId = 0): Collection
    {
        $branch = new Collection();

        foreach ($categories as $category) {
            if ((int) $category->parent_id === $parentId) {
                $children = self::buildTree($categories, $category->id);
                if ($children->isNotEmpty()) {
                    $category->setRelation('children', $children);
                }
                $branch->push($category);
            }
        }

        return $branch;
    }
}
