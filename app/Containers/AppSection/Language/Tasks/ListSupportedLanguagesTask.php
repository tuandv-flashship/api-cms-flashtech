<?php

namespace App\Containers\AppSection\Language\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use App\Ship\Supports\Language as LanguageSupport;

final class ListSupportedLanguagesTask extends ParentTask
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(): array
    {
        $available = array_map(
            static function (array $language): array {
                return [
                    'lang_name' => $language['name'] ?? null,
                    'lang_locale' => $language['locale'] ?? null,
                    'lang_code' => $language['code'] ?? null,
                    'lang_flag' => $language['flag'] ?? null,
                    'lang_is_rtl' => (bool) ($language['is_rtl'] ?? false),
                ];
            },
            LanguageSupport::getAvailableLocales(true),
        );

        return array_values($available);
    }
}
