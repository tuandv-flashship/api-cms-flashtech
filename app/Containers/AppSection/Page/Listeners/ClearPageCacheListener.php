<?php

namespace App\Containers\AppSection\Page\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

final class ClearPageCacheListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        if (isset($event->pageData['id'])) {
             Cache::forget('page_' . $event->pageData['id']);
        } elseif (isset($event->page)) {
             Cache::forget('page_' . $event->page->getKey());
        }
    }
}
