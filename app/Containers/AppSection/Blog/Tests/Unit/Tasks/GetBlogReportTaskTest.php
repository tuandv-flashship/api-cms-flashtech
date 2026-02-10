<?php

namespace App\Containers\AppSection\Blog\Tests\Unit\Tasks;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tasks\GetBlogReportTask;
use App\Containers\AppSection\Blog\Tests\UnitTestCase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GetBlogReportTask::class)]
final class GetBlogReportTaskTest extends UnitTestCase
{
    private GetBlogReportTask $task;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->task = app(GetBlogReportTask::class);
    }

    public function testReportContainsExpectedStructure(): void
    {
        $report = $this->task->run();

        $this->assertArrayHasKey('totals', $report);
        $this->assertArrayHasKey('statuses', $report);
        $this->assertArrayHasKey('top_viewed_posts', $report);
        $this->assertArrayHasKey('recent_posts', $report);
        $this->assertArrayHasKey('posts_per_category', $report);

        $this->assertArrayHasKey('posts', $report['totals']);
        $this->assertArrayHasKey('views', $report['totals']);
        $this->assertArrayHasKey('categories', $report['totals']);
        $this->assertArrayHasKey('tags', $report['totals']);

        $this->assertArrayHasKey('published', $report['statuses']);
        $this->assertArrayHasKey('draft', $report['statuses']);
        $this->assertArrayHasKey('pending', $report['statuses']);
    }

    public function testReportCountsTotalsCorrectly(): void
    {
        Post::factory()->count(3)->create(['status' => 'published', 'views' => 100]);
        Post::factory()->count(2)->create(['status' => 'draft', 'views' => 50]);
        Post::factory()->count(1)->create(['status' => 'pending', 'views' => 10]);
        Category::factory()->count(4)->create();
        Tag::factory()->count(5)->create();

        $report = $this->task->run();

        $this->assertSame(6, $report['totals']['posts']);
        $this->assertSame(410, $report['totals']['views']); // 3*100 + 2*50 + 1*10
        $this->assertSame(4, $report['totals']['categories']);
        $this->assertSame(5, $report['totals']['tags']);
    }

    public function testReportCountsStatusesCorrectly(): void
    {
        Post::factory()->count(3)->create(['status' => 'published']);
        Post::factory()->count(2)->create(['status' => 'draft']);
        Post::factory()->count(1)->create(['status' => 'pending']);

        $report = $this->task->run();

        $this->assertSame(3, $report['statuses']['published']);
        $this->assertSame(2, $report['statuses']['draft']);
        $this->assertSame(1, $report['statuses']['pending']);
    }

    public function testReportReturnsZeroStatusesWhenNoPosts(): void
    {
        $report = $this->task->run();

        $this->assertSame(0, $report['statuses']['published']);
        $this->assertSame(0, $report['statuses']['draft']);
        $this->assertSame(0, $report['statuses']['pending']);
    }

    public function testTopViewedPostsAreSortedByViews(): void
    {
        Post::factory()->create(['views' => 10, 'name' => 'Low Views']);
        Post::factory()->create(['views' => 1000, 'name' => 'High Views']);
        Post::factory()->create(['views' => 500, 'name' => 'Medium Views']);

        $report = $this->task->run();

        $topPosts = $report['top_viewed_posts'];
        $this->assertCount(3, $topPosts);
        $this->assertSame('High Views', $topPosts[0]['name']);
        $this->assertSame(1000, $topPosts[0]['views']);
        $this->assertSame('Medium Views', $topPosts[1]['name']);
        $this->assertSame('Low Views', $topPosts[2]['name']);
    }

    public function testRecentPostsAreSortedByCreatedAtDesc(): void
    {
        Post::factory()->create([
            'name' => 'Old Post',
            'created_at' => now()->subDays(10),
        ]);
        Post::factory()->create([
            'name' => 'New Post',
            'created_at' => now(),
        ]);

        $report = $this->task->run();

        $recentPosts = $report['recent_posts'];
        $this->assertCount(2, $recentPosts);
        $this->assertSame('New Post', $recentPosts[0]['name']);
        $this->assertSame('Old Post', $recentPosts[1]['name']);
    }

    public function testTopViewedPostsLimitedTo10(): void
    {
        Post::factory()->count(15)->create(['views' => 100]);

        $report = $this->task->run();

        $this->assertCount(10, $report['top_viewed_posts']);
    }

    public function testPostsPerCategoryIncludesPostCount(): void
    {
        $category = Category::factory()->create(['name' => 'Tech']);
        Post::factory()->count(5)->create()->each(function (Post $post) use ($category): void {
            $post->categories()->attach($category->id);
        });

        $report = $this->task->run();

        $this->assertNotEmpty($report['posts_per_category']);
        $techCategory = collect($report['posts_per_category'])->firstWhere('name', 'Tech');
        $this->assertNotNull($techCategory);
        $this->assertSame(5, $techCategory['posts_count']);
    }

    public function testReportIsCachedAndServesFromCache(): void
    {
        Post::factory()->count(3)->create();

        // First call — populates cache
        $report1 = $this->task->run();
        $this->assertSame(3, $report1['totals']['posts']);

        // Add more posts (should not affect cached result)
        Post::factory()->count(2)->create();

        // Second call — should return cached data
        $report2 = $this->task->run();
        $this->assertSame(3, $report2['totals']['posts']);
    }

    public function testReportCacheCanBeInvalidated(): void
    {
        Post::factory()->count(3)->create();

        $report1 = $this->task->run();
        $this->assertSame(3, $report1['totals']['posts']);

        Post::factory()->count(2)->create();

        // Flush cache to simulate invalidation
        Cache::flush();

        $report2 = $this->task->run();
        $this->assertSame(5, $report2['totals']['posts']);
    }

    public function testPostFormatIncludesRequiredFields(): void
    {
        Post::factory()->create([
            'name' => 'Format Test Post',
            'views' => 42,
        ]);

        $report = $this->task->run();

        $post = $report['top_viewed_posts'][0];
        $this->assertArrayHasKey('id', $post);
        $this->assertArrayHasKey('name', $post);
        $this->assertArrayHasKey('views', $post);
        $this->assertArrayHasKey('slug', $post);
        $this->assertArrayHasKey('created_at', $post);
        $this->assertArrayHasKey('categories', $post);
        $this->assertSame('Format Test Post', $post['name']);
        $this->assertSame(42, $post['views']);
    }

    public function testCategoryFormatIncludesRequiredFields(): void
    {
        $category = Category::factory()->create(['name' => 'Science']);

        $report = $this->task->run();

        $categoryData = $report['posts_per_category'][0] ?? null;
        $this->assertNotNull($categoryData);
        $this->assertArrayHasKey('id', $categoryData);
        $this->assertArrayHasKey('name', $categoryData);
        $this->assertArrayHasKey('posts_count', $categoryData);
    }
}
