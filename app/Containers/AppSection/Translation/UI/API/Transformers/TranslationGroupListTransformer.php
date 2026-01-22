<?php

namespace App\Containers\AppSection\Translation\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class TranslationGroupListTransformer extends ParentTransformer
{
    /**
     * @param object|array<string, mixed> $payload
     */
    public function transform(object|array $payload): array
    {
        $data = (array) $payload;

        return [
            'type' => 'TranslationGroupList',
            'id' => (string) ($data['locale'] ?? ''),
            'locale' => $data['locale'] ?? null,
            'groups' => array_values(array_filter((array) ($data['groups'] ?? []))),
        ];
    }
}
