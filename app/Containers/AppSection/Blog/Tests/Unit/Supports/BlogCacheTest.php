<?php

namespace App\Containers\AppSection\Blog\Tests\Unit\Supports;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Supports\BlogCache;
use App\Containers\AppSection\Blog\Tests\UnitTestCase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogCache::class)]
final class BlogCacheTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testGetPublishedCategoriesReturnsCachedData(): void
    {
        Category::factory()->count(3)->create(['status' => 'published']);

        $first = BlogCache::getPublishedCategories();
        $this->assertCount(3, $first);

        // Create more categories — should still return cached
        Category::factory()->count(2)->create(['status' => 'published']);
        $second = BlogCache::getPublishedCategories();
        $this->assertCount(3, $second);
    }

    public function testGetPublishedTagsReturnsCachedData(): void
    {
        Tag::factory()->count(4)->create(['status' => 'published']);

        $first = BlogCache::getPublishedTags();
        $this->assertCount(4, $first);

        Tag::factory()->count(2)->create(['status' => 'published']);
        $second = BlogCache::getPublishedTags();
        $this->assertCount(4, $second);
    }

    public function testForgetCategoriesClearsCache(): void
    {
        Category::factory()->count(3)->create(['status' => 'published']);
        BlogCache::getPublishedCategories();

        Category::factory()->count(2)->create(['status' => 'published']);

        BlogCache::forgetCategories();

        $refreshed = BlogCache::getPublishedCategories();
        $this->assertCount(5, $refreshed);
    }

    public function testForgetTagsClearsCache(): void
    {
        Tag::factory()->count(3)->create(['status' => 'published']);
        BlogCache::getPublishedTags();

        Tag::factory()->count(2)->create(['status' => 'published']);

        BlogCache::forgetTags();

        $refreshed = BlogCache::getPublishedTags();
        $this->assertCount(5, $refreshed);
    }

    public function testGetCategoryReturnsCachedSingleCategory(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);

        $cached = BlogCache::getCategory($category->id);
        $this->assertNotNull($cached);
        $this->assertSame('Test Category', $cached->name);
    }

    public function testGetCategoryReturnsNullForNonExistent(): void
    {
        $result = BlogCache::getCategory(999999);
        $this->assertNull($result);
    }

    public function testGetTagReturnsCachedSingleTag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Test Tag']);

        $cached = BlogCache::getTag($tag->id);
        $this->assertNotNull($cached);
        $this->assertSame('Test Tag', $cached->name);
    }

    public function testForgetAllClearsBothCategoriesAndTags(): void
    {
        Category::factory()->count(2)->create(['status' => 'published']);
        Tag::factory()->count(2)->create(['status' => 'published']);

        BlogCache::getPublishedCategories();
        BlogCache::getPublishedTags();

        // Add more
        Category::factory()->count(3)->create(['status' => 'published']);
        Tag::factory()->count(3)->create(['status' => 'published']);

        BlogCache::forgetAll();

        $this->assertCount(5, BlogCache::getPublishedCategories());
        $this->assertCount(5, BlogCache::getPublishedTags());
    }

    public function testForgetCategoryAlsoClearsAllCategories(): void
    {
        $category = Category::factory()->create(['status' => 'published']);
        BlogCache::getPublishedCategories();
        BlogCache::getCategory($category->id);

        Category::factory()->count(2)->create(['status' => 'published']);

        BlogCache::forgetCategory($category->id);

        // All categories cache should be cleared too
        $this->assertCount(3, BlogCache::getPublishedCategories());
    }

    public function testCategoryTreeReturnsCachedTreeStructure(): void
    {
        $parent = Category::factory()->create(['name' => 'Parent', 'parent_id' => 0, 'status' => 'published']);
        Category::factory()->create(['name' => 'Child', 'parent_id' => $parent->id, 'status' => 'published']);

        $tree = BlogCache::getCategoriesTree();
        $this->assertNotEmpty($tree);

        // Should have parent at root level
        $parentNode = $tree->firstWhere('name', 'Parent');
        $this->assertNotNull($parentNode);
    }
}
