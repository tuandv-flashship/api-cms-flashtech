<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class PostViewTransformer extends ParentTransformer
{
    /**
     * @param object|array<string, mixed> $payload
     */
    public function transform(object|array $payload): array
    {
        $data = (array) $payload;

        return [
            'type' => 'PostView',
            'id' => 'post-view',
            'recorded' => (bool) ($data['recorded'] ?? false),
            'views' => (int) ($data['views'] ?? 0),
        ];
    }
}
