<?php

namespace App\Containers\AppSection\Page\Events;

use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Parents\Events\Event;

final class PageDeletedEvent extends Event
{
    public function __construct(
        public array $pageData,
    ) {
    }
}
