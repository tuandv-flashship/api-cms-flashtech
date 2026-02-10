<?php

namespace App\Containers\AppSection\Page\Events;

use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Parents\Events\Event;
use Illuminate\Queue\SerializesModels;

final class PageCreatedEvent extends Event
{
    use SerializesModels;

    public function __construct(
        public Page $page,
    ) {
    }
}
