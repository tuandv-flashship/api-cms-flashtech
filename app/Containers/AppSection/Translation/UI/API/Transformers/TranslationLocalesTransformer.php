<?php

namespace App\Containers\AppSection\Translation\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class TranslationLocalesTransformer extends ParentTransformer
{
    /**
     * @param object|array<string, mixed> $payload
     */
    public function transform(object|array $payload): array
    {
        $data = (array) $payload;

        $installed = $this->mapLocales($data['installed'] ?? []);
        $available = $this->mapLocales($data['available'] ?? []);

        return [
            'type' => 'TranslationLocales',
            'id' => 'locales',
            'installed' => $installed,
            'available' => $available,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $locales
     * @return array<int, array<string, mixed>>
     */
    private function mapLocales(array $locales): array
    {
        $mapped = [];

        foreach ($locales as $locale) {
            if (! is_array($locale)) {
                continue;
            }

            $mapped[] = [
                'type' => 'TranslationLocale',
                'id' => (string) ($locale['locale'] ?? ''),
                'locale' => $locale['locale'] ?? null,
                'code' => $locale['code'] ?? null,
                'name' => $locale['name'] ?? null,
                'flag' => $locale['flag'] ?? null,
                'flag_img' => env('APP_URL') . '/images/flags/' . ($locale['flag'] ?? '-') . '.svg',
                'is_rtl' => (bool) ($locale['is_rtl'] ?? false),
                'installed' => (bool) ($locale['installed'] ?? false),
            ];
        }

        return $mapped;
    }
}
