<?php

namespace App\Containers\AppSection\Tools\Supports\Concerns;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;

/**
 * Shared locale/language helpers for translation exporters and importers.
 */
trait TranslationLocaleHelper
{
    /**
     * Non-default locale map: lang_code => normalized suffix.
     *
     * @return array<string, string>
     */
    protected function getLocaleMap(): array
    {
        $default = $this->getDefaultLangCode();
        $map = [];

        foreach ($this->getSupportedLangCodes() as $langCode) {
            if ($default && $langCode === $default) {
                continue;
            }

            $map[$langCode] = $this->normalizeLangKey($langCode);
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    protected function getSupportedLangCodes(): array
    {
        $codes = Language::query()
            ->orderBy('lang_order')
            ->pluck('lang_code')
            ->filter()
            ->all();

        return $codes !== [] ? $codes : [config('app.locale', 'en')];
    }

    protected function getDefaultLangCode(): ?string
    {
        return LanguageAdvancedManager::getDefaultLocaleCode() ?: config('app.locale');
    }

    /**
     * @return array<int, string>
     */
    protected function getTranslatableColumnsFor(string $modelClass): array
    {
        return LanguageAdvancedManager::getTranslatableColumns($modelClass);
    }

    protected function normalizeLangKey(string $langCode): string
    {
        return strtolower(str_replace('-', '_', $langCode));
    }

    protected function maxLengthForColumn(string $column): int
    {
        return match ($column) {
            'description' => 400,
            'content' => 300000,
            default => 255,
        };
    }
}
