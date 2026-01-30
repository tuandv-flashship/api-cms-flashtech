<?php

namespace App\Containers\AppSection\CustomField\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class FieldItemTransformer extends ParentTransformer
{
    /**
     * @param array<string, mixed> $item
     */
    public function transform(array $item): array
    {
        $items = $item['items'] ?? [];
        if (! is_array($items)) {
            $items = [];
        }

        $item['items'] = $items;

        return $item;
    }
}
