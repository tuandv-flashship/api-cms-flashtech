<?php

namespace App\Containers\AppSection\Language\UI\API\Transformers;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class LanguageTransformer extends ParentTransformer
{
    public function transform(Language $language): array
    {
        return [
            'type' => $language->getResourceKey(),
            'id' => $language->getHashedKey(),
            'lang_name' => $language->lang_name,
            'lang_locale' => $language->lang_locale,
            'lang_code' => $language->lang_code,
            'lang_flag' => $language->lang_flag,
            'lang_flag_img' => env('APP_URL') . '/images/flags/' . $language->lang_flag . '.svg',
            'lang_is_default' => $language->lang_is_default,
            'lang_is_rtl' => $language->lang_is_rtl,
            'lang_order' => $language->lang_order,
        ];
    }
}
