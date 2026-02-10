<?php

namespace App\Containers\AppSection\Page\Providers;

use App\Containers\AppSection\Page\Events\PageCreatedEvent;
use App\Containers\AppSection\Page\Events\PageDeletedEvent;
use App\Containers\AppSection\Page\Events\PageUpdatedEvent;
use App\Containers\AppSection\Page\Listeners\ClearPageCacheListener;
use App\Ship\Parents\Providers\EventServiceProvider as ParentEventServiceProvider;

class EventServiceProvider extends ParentEventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        PageCreatedEvent::class => [
            ClearPageCacheListener::class,
        ],
        PageUpdatedEvent::class => [
            ClearPageCacheListener::class,
        ],
        PageDeletedEvent::class => [
            ClearPageCacheListener::class,
        ],
    ];
}
