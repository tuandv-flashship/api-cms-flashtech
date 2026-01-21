<?php

namespace App\Containers\AppSection\Language\Data\Seeders;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Language\Tasks\CreateLanguageTask;
use App\Ship\Parents\Seeders\Seeder as ParentSeeder;
use App\Ship\Supports\Language as LanguageSupport;

final class LanguageSeeder_1 extends ParentSeeder
{
    public function run(CreateLanguageTask $task): void
    {
        $available = LanguageSupport::getAvailableLocales(true);

        $englishConfig = $this->findAvailableLanguage($available, 'en_US', 'en');
        $vietnameseConfig = $this->findAvailableLanguage($available, 'vi', 'vi');

        if (! $this->languageExists('en_US', 'en')) {
            $english = $this->buildLanguageData($englishConfig, [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_flag' => 'us',
                'lang_is_rtl' => false,
            ]);
            $english['lang_is_default'] = false;
            $english['lang_order'] = 0;

            $task->run($english);
        }

        if (! $this->languageExists('vi', 'vi')) {
            $vietnamese = $this->buildLanguageData($vietnameseConfig, [
                'lang_name' => 'Vietnamese',
                'lang_locale' => 'vi',
                'lang_code' => 'vi',
                'lang_flag' => 'vn',
                'lang_is_rtl' => false,
            ]);
            $vietnamese['lang_is_default'] = true;
            $vietnamese['lang_order'] = 1;

            $task->run($vietnamese);
        }

        $this->setDefaultLanguage('vi', 'vi');
    }

    /**
     * @param array<int, mixed> $available
     * @return array<string, mixed>|null
     */
    private function findAvailableLanguage(array $available, string $code, string $locale): array|null
    {
        foreach ($available as $language) {
            if (! is_array($language)) {
                continue;
            }

            if (($language['code'] ?? null) === $code || ($language['locale'] ?? null) === $locale) {
                return $this->mapAvailableLanguage($language);
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $language
     * @return array<string, mixed>
     */
    private function mapAvailableLanguage(array $language): array
    {
        return [
            'lang_name' => $language['name'] ?? null,
            'lang_locale' => $language['locale'] ?? null,
            'lang_code' => $language['code'] ?? null,
            'lang_flag' => $language['flag'] ?? null,
            'lang_is_rtl' => (bool) ($language['is_rtl'] ?? false),
        ];
    }

    /**
     * @param array<string, mixed>|null $config
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function buildLanguageData(array|null $config, array $fallback): array
    {
        if (! $config) {
            return $fallback;
        }

        return [
            'lang_name' => $config['lang_name'] ?? $fallback['lang_name'],
            'lang_locale' => $config['lang_locale'] ?? $fallback['lang_locale'],
            'lang_code' => $config['lang_code'] ?? $fallback['lang_code'],
            'lang_flag' => $config['lang_flag'] ?? $fallback['lang_flag'],
            'lang_is_rtl' => (bool) ($config['lang_is_rtl'] ?? $fallback['lang_is_rtl']),
        ];
    }

    private function languageExists(string $code, string $locale): bool
    {
        return Language::query()
            ->where('lang_code', $code)
            ->orWhere('lang_locale', $locale)
            ->exists();
    }

    private function setDefaultLanguage(string $code, string $locale): void
    {
        $language = Language::query()
            ->where('lang_code', $code)
            ->orWhere('lang_locale', $locale)
            ->first();

        if (! $language) {
            return;
        }

        Language::query()->update(['lang_is_default' => 0]);
        Language::query()
            ->where('lang_id', $language->lang_id)
            ->update(['lang_is_default' => 1]);
    }
}
