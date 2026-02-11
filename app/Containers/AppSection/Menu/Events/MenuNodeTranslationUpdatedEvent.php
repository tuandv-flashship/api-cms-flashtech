<?php

namespace App\Containers\AppSection\Menu\Events;

use App\Ship\Parents\Events\Event;

final class MenuNodeTranslationUpdatedEvent extends Event
{
    public function __construct(
        public readonly int $menuId,
        public readonly string $langCode,
    ) {
    }
}
