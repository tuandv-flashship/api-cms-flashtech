<?php

namespace App\Containers\AppSection\Translation\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class TranslationGroupTransformer extends ParentTransformer
{
    /**
     * @param object|array<string, mixed> $payload
     */
    public function transform(object|array $payload): array
    {
        $data = (array) $payload;
        $locale = (string) ($data['locale'] ?? '');
        $group = (string) ($data['group'] ?? '');

        return [
            'type' => 'TranslationGroup',
            'id' => $locale . ':' . $group,
            'locale' => $locale ?: null,
            'group' => $group ?: null,
            'translations' => (array) ($data['translations'] ?? []),
        ];
    }
}
