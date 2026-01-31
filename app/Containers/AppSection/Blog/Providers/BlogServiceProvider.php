<?php

namespace App\Containers\AppSection\Blog\Providers;

use App\Containers\AppSection\Blog\Events\CategoryCreated;
use App\Containers\AppSection\Blog\Events\CategoryDeleted;
use App\Containers\AppSection\Blog\Events\CategoryUpdated;
use App\Containers\AppSection\Blog\Events\TagCreated;
use App\Containers\AppSection\Blog\Events\TagDeleted;
use App\Containers\AppSection\Blog\Events\TagUpdated;
use App\Containers\AppSection\Blog\Listeners\CacheInvalidationListener;
use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Blog Service Provider.
 *
 * Events are dispatched from Actions for extensibility.
 * Cache invalidation is handled automatically via CacheInvalidationListener.
 */
final class BlogServiceProvider extends ParentServiceProvider
{
    public function boot(): void
    {
        $this->registerCacheInvalidationListeners();
    }

    private function registerCacheInvalidationListeners(): void
    {
        // Category cache invalidation
        Event::listen(CategoryCreated::class, [CacheInvalidationListener::class, 'handleCategoryCreated']);
        Event::listen(CategoryUpdated::class, [CacheInvalidationListener::class, 'handleCategoryUpdated']);
        Event::listen(CategoryDeleted::class, [CacheInvalidationListener::class, 'handleCategoryDeleted']);

        // Tag cache invalidation
        Event::listen(TagCreated::class, [CacheInvalidationListener::class, 'handleTagCreated']);
        Event::listen(TagUpdated::class, [CacheInvalidationListener::class, 'handleTagUpdated']);
        Event::listen(TagDeleted::class, [CacheInvalidationListener::class, 'handleTagDeleted']);
    }
}

