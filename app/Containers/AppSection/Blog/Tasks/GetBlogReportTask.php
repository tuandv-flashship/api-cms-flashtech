<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class GetBlogReportTask extends ParentTask
{
    private const CACHE_KEY = 'blog:report';
    private const CACHE_TTL_SECONDS = 300; // 5 minutes

    /**
     * @return array<string, mixed>
     */
    public function run(): array
    {
        $langCode = LanguageAdvancedManager::getTranslationLocale();
        $cacheKey = self::CACHE_KEY . ':' . $langCode;

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($langCode) {
            return $this->buildReport($langCode);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReport(string $langCode): array
    {
        // Consolidate totals: 2 queries instead of 4
        $postAggregates = Post::query()
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(views), 0) as total_views')
            ->first();
        $totalPosts = (int) $postAggregates->total;
        $totalViews = (int) $postAggregates->total_views;
        $totalCategories = Category::query()->count();
        $totalTags = Tag::query()->count();

        // Consolidate status counts: 1 query instead of 3
        $statusCounts = Post::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $categoryWith = LanguageAdvancedManager::withTranslations([], Category::class, $langCode);
        $postWith = LanguageAdvancedManager::withTranslations([
            'slugable',
            'categories' => static function ($query) use ($categoryWith): void {
                if ($categoryWith !== []) {
                    $query->with($categoryWith);
                }
            },
        ], Post::class, $langCode);

        $topViewedPosts = Post::query()
            ->select(['id', 'name', 'views', 'created_at'])
            ->with($postWith)
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $recentPosts = Post::query()
            ->select(['id', 'name', 'views', 'created_at'])
            ->with($postWith)
            ->latest()
            ->limit(10)
            ->get();

        $postsPerCategory = Category::query()
            ->select(['id', 'name'])
            ->with($categoryWith)
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(10)
            ->get();

        return [
            'totals' => [
                'posts' => $totalPosts,
                'views' => $totalViews,
                'categories' => $totalCategories,
                'tags' => $totalTags,
            ],
            'statuses' => [
                'published' => (int) ($statusCounts[ContentStatus::PUBLISHED->value] ?? 0),
                'draft' => (int) ($statusCounts[ContentStatus::DRAFT->value] ?? 0),
                'pending' => (int) ($statusCounts[ContentStatus::PENDING->value] ?? 0),
            ],
            'top_viewed_posts' => $this->formatPosts($topViewedPosts),
            'recent_posts' => $this->formatPosts($recentPosts),
            'posts_per_category' => $this->formatCategories($postsPerCategory),
        ];
    }

    /**
     * @param Collection<int, Post> $posts
     * @return array<int, array<string, mixed>>
     */
    private function formatPosts(Collection $posts): array
    {
        return $posts->map(function (Post $post): array {
            return [
                'id' => $this->hashId($post->getKey()),
                'name' => $post->name,
                'views' => (int) $post->views,
                'slug' => $post->slug,
                'created_at' => $post->created_at?->toISOString(),
                'categories' => $this->formatCategoryItems($post->categories),
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, Category> $categories
     * @return array<int, array<string, mixed>>
     */
    private function formatCategories(Collection $categories): array
    {
        return $categories->map(function (Category $category): array {
            return [
                'id' => $this->hashId($category->getKey()),
                'name' => $category->name,
                'posts_count' => (int) $category->posts_count,
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, Category> $categories
     * @return array<int, array<string, mixed>>
     */
    private function formatCategoryItems(Collection $categories): array
    {
        return $categories->map(function (Category $category): array {
            return [
                'id' => $this->hashId($category->getKey()),
                'name' => $category->name,
            ];
        })->values()->all();
    }
}

