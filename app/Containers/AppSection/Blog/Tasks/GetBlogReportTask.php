<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Collection;

final class GetBlogReportTask extends ParentTask
{
    /**
     * @return array<string, mixed>
     */
    public function run(): array
    {
        $totalPosts = Post::query()->count();
        $totalViews = (int) Post::query()->sum('views');
        $totalCategories = Category::query()->count();
        $totalTags = Tag::query()->count();

        $publishedPosts = Post::query()->where('status', ContentStatus::PUBLISHED)->count();
        $draftPosts = Post::query()->where('status', ContentStatus::DRAFT)->count();
        $pendingPosts = Post::query()->where('status', ContentStatus::PENDING)->count();

        $langCode = LanguageAdvancedManager::getTranslationLocale();
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
                'published' => $publishedPosts,
                'draft' => $draftPosts,
                'pending' => $pendingPosts,
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

    private function hashId(int|string|null $id): int|string|null
    {
        if ($id === null) {
            return null;
        }

        $intId = (int) $id;
        if ($intId <= 0) {
            return $intId;
        }

        return config('apiato.hash-id') ? hashids()->encodeOrFail($intId) : $intId;
    }
}
