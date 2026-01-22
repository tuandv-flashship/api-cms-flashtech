<?php

namespace App\Containers\AppSection\Translation\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class TranslationLocaleStatusTransformer extends ParentTransformer
{
    /**
     * @param object|array<string, mixed> $payload
     */
    public function transform(object|array $payload): array
    {
        $data = (array) $payload;

        return [
            'type' => 'TranslationLocaleStatus',
            'id' => (string) ($data['locale'] ?? ''),
            'locale' => $data['locale'] ?? null,
            'downloaded' => (bool) ($data['downloaded'] ?? false),
            'copied' => (bool) ($data['copied'] ?? false),
        ];
    }
}
