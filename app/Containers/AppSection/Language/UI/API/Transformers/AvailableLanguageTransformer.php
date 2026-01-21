<?php

namespace App\Containers\AppSection\Language\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class AvailableLanguageTransformer extends ParentTransformer
{
    /**
     * @param array<string, mixed> $language
     */
    public function transform(array $language): array
    {
        return [
            'type' => 'AvailableLanguage',
            'id' => (string) ($language['lang_code'] ?? ''),
            'lang_name' => $language['lang_name'] ?? null,
            'lang_locale' => $language['lang_locale'] ?? null,
            'lang_code' => $language['lang_code'] ?? null,
            'lang_flag' => $language['lang_flag'] ?? null,
            'lang_flag_img' => env('APP_URL') . '/images/flags/' . ($language['lang_flag'] ?? '-') . '.svg',
            'lang_is_rtl' => (bool) ($language['lang_is_rtl'] ?? false),
        ];
    }
}
