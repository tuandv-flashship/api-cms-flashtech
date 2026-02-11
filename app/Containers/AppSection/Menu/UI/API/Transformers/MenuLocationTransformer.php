<?php

namespace App\Containers\AppSection\Menu\UI\API\Transformers;

use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class MenuLocationTransformer extends ParentTransformer
{
    public function transform(MenuLocation $location): array
    {
        return [
            'type' => $location->getResourceKey(),
            'id' => $location->getHashedKey(),
            'menu_id' => $this->hashId((int) $location->menu_id),
            'location' => $location->location,
            'created_at' => $location->created_at?->toISOString(),
            'updated_at' => $location->updated_at?->toISOString(),
        ];
    }
}
