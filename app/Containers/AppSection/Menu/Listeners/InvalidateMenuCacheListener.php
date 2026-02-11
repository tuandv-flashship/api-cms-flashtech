<?php

namespace App\Containers\AppSection\Menu\Listeners;

use App\Containers\AppSection\Menu\Events\MenuDeletedEvent;
use App\Containers\AppSection\Menu\Events\MenuNodeTranslationUpdatedEvent;
use App\Containers\AppSection\Menu\Events\MenuSavedEvent;
use App\Containers\AppSection\Menu\Supports\MenuCache;

final class InvalidateMenuCacheListener
{
    public function __construct(
        private readonly MenuCache $menuCache,
    ) {
    }

    public function handleMenuSaved(MenuSavedEvent $event): void
    {
        $this->menuCache->forgetByMenuId($event->menuId);
    }

    public function handleMenuDeleted(MenuDeletedEvent $event): void
    {
        if ($event->locations !== []) {
            foreach ($event->locations as $location) {
                $this->menuCache->forgetByLocation($location);
            }

            return;
        }

        $this->menuCache->forgetByMenuId($event->menuId);
    }

    public function handleTranslationUpdated(MenuNodeTranslationUpdatedEvent $event): void
    {
        $this->menuCache->forgetByMenuId($event->menuId);
    }
}
