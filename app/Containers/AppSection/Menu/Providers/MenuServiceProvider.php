<?php

namespace App\Containers\AppSection\Menu\Providers;

use App\Containers\AppSection\Blog\Events\CategoryDeleted;
use App\Containers\AppSection\Blog\Events\CategoryUpdated;
use App\Containers\AppSection\Blog\Events\PostDeleted;
use App\Containers\AppSection\Blog\Events\PostUpdated;
use App\Containers\AppSection\Blog\Events\TagDeleted;
use App\Containers\AppSection\Blog\Events\TagUpdated;
use App\Containers\AppSection\Menu\Events\MenuDeletedEvent;
use App\Containers\AppSection\Menu\Events\MenuNodeTranslationUpdatedEvent;
use App\Containers\AppSection\Menu\Events\MenuSavedEvent;
use App\Containers\AppSection\Menu\Listeners\HandleDeletedReferenceForMenuNodeListener;
use App\Containers\AppSection\Menu\Listeners\InvalidateMenuCacheListener;
use App\Containers\AppSection\Menu\Listeners\UpdateMenuNodeUrlListener;
use App\Containers\AppSection\Page\Events\PageDeletedEvent;
use App\Containers\AppSection\Page\Events\PageUpdatedEvent;
use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;
use Illuminate\Support\Facades\Event;

final class MenuServiceProvider extends ParentServiceProvider
{
    private static bool $listenersRegistered = false;

    public function boot(): void
    {
        if (self::$listenersRegistered) {
            return;
        }

        self::$listenersRegistered = true;

        Event::listen(MenuSavedEvent::class, [InvalidateMenuCacheListener::class, 'handleMenuSaved']);
        Event::listen(MenuDeletedEvent::class, [InvalidateMenuCacheListener::class, 'handleMenuDeleted']);
        Event::listen(MenuNodeTranslationUpdatedEvent::class, [InvalidateMenuCacheListener::class, 'handleTranslationUpdated']);

        Event::listen(PageUpdatedEvent::class, [UpdateMenuNodeUrlListener::class, 'handle']);
        Event::listen(PostUpdated::class, [UpdateMenuNodeUrlListener::class, 'handle']);
        Event::listen(CategoryUpdated::class, [UpdateMenuNodeUrlListener::class, 'handle']);
        Event::listen(TagUpdated::class, [UpdateMenuNodeUrlListener::class, 'handle']);

        Event::listen(PageDeletedEvent::class, [HandleDeletedReferenceForMenuNodeListener::class, 'handle']);
        Event::listen(PostDeleted::class, [HandleDeletedReferenceForMenuNodeListener::class, 'handle']);
        Event::listen(CategoryDeleted::class, [HandleDeletedReferenceForMenuNodeListener::class, 'handle']);
        Event::listen(TagDeleted::class, [HandleDeletedReferenceForMenuNodeListener::class, 'handle']);
    }
}
