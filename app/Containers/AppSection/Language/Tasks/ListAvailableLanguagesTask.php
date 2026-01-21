<?php

namespace App\Containers\AppSection\Language\Tasks;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Supports\Language as LanguageSupport;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ListAvailableLanguagesTask extends ParentTask
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

        $existingCodes = Language::query()->pluck('lang_code')->all();
        $existingLocales = Language::query()->pluck('lang_locale')->all();

        $filtered = array_filter($available, static function (mixed $language) use ($existingCodes, $existingLocales): bool {
            if (! is_array($language)) {
                return false;
            }

            $code = $language['lang_code'] ?? null;
            $locale = $language['lang_locale'] ?? null;

            if (! $code || ! $locale) {
                return false;
            }

            if (in_array($code, $existingCodes, true) || in_array($locale, $existingLocales, true)) {
                return false;
            }

            return true;
        });

        return array_values($filtered);
    }
}
