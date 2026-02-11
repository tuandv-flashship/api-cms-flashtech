<?php

namespace App\Containers\AppSection\Menu\Events;

use App\Ship\Parents\Events\Event;

final class MenuDeletedEvent extends Event
{
    public function __construct(
        public readonly int $menuId,
        /** @var array<int, string> */
        public readonly array $locations = [],
    ) {
    }
}
