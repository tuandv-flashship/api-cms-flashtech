<?php

namespace App\Containers\AppSection\Blog\Listeners;

use App\Containers\AppSection\Blog\Events\CategoryCreated;
use App\Containers\AppSection\Blog\Events\CategoryDeleted;
use App\Containers\AppSection\Blog\Events\CategoryUpdated;
use App\Containers\AppSection\Blog\Events\TagCreated;
use App\Containers\AppSection\Blog\Events\TagDeleted;
use App\Containers\AppSection\Blog\Events\TagUpdated;
use App\Containers\AppSection\Blog\Supports\BlogCache;

/**
 * Listener to invalidate blog caches when data changes.
 */
final class CacheInvalidationListener
{
    public function handleCategoryCreated(CategoryCreated $event): void
    {
        BlogCache::forgetCategories();
    }

    public function handleCategoryUpdated(CategoryUpdated $event): void
    {
        BlogCache::forgetCategory($event->category->id);
    }

    public function handleCategoryDeleted(CategoryDeleted $event): void
    {
        BlogCache::forgetCategory($event->categoryId);
    }

    public function handleTagCreated(TagCreated $event): void
    {
        BlogCache::forgetTags();
    }

    public function handleTagUpdated(TagUpdated $event): void
    {
        BlogCache::forgetTag($event->tag->id);
    }

    public function handleTagDeleted(TagDeleted $event): void
    {
        BlogCache::forgetTag($event->tagId);
    }
}
