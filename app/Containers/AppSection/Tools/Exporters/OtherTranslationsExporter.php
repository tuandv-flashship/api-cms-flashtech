<?php

namespace App\Containers\AppSection\Tools\Exporters;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Tools\Supports\Export\ExportColumn;
use App\Containers\AppSection\Tools\Supports\Export\Exporter;
use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;

final class OtherTranslationsExporter extends Exporter
{
    public function __construct(private readonly TranslationFilesystem $filesystem)
    {
    }

    /**
     * @return array<int, ExportColumn>
     */
    public function columns(): array
    {
        $columns = [
            ExportColumn::make('key')->label('key'),
        ];

        foreach ($this->getLocales() as $locale) {
            $columns[] = ExportColumn::make($this->normalizeLocaleKey($locale))->label($locale);
        }

        return $columns;
    }

    /**
     * @return iterable<int, array<string, mixed>>
     */
    protected function getRows(): iterable
    {
        $defaultLocale = $this->getDefaultLocale();
        $locales = $this->getLocales();

        $groups = $this->filesystem->listGroups($defaultLocale);
        foreach ($groups as $group) {
            $baseTranslations = $this->filesystem->readTranslations($defaultLocale, $group);
            if ($baseTranslations === []) {
                continue;
            }

            $groupKey = $this->normalizeGroupKey($group);

            $localeTranslations = [];
            foreach ($locales as $locale) {
                $localeTranslations[$locale] = $this->filesystem->readTranslations($locale, $group);
            }

            foreach ($baseTranslations as $key => $value) {
                $row = [
                    'key' => $groupKey . '::' . $key,
                ];

                foreach ($locales as $locale) {
                    $localeKey = $this->normalizeLocaleKey($locale);
                    $row[$localeKey] = $localeTranslations[$locale][$key] ?? '';
                }

                yield $row;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function getLocales(): array
    {
        $locales = Language::query()
            ->orderBy('lang_order')
            ->get(['lang_locale', 'lang_code'])
            ->map(fn (Language $language) => $language->lang_locale ?: $language->lang_code)
            ->filter()
            ->values()
            ->all();

        if ($locales === []) {
            $locales = [config('app.locale', 'en')];
        }

        return $locales;
    }

    private function getDefaultLocale(): string
    {
        $default = Language::query()
            ->where('lang_is_default', true)
            ->value('lang_locale');

        return $default ?: config('app.locale', 'en');
    }

    private function normalizeGroupKey(string $group): string
    {
        return str_starts_with($group, 'app/') ? substr($group, 4) : $group;
    }

    private function normalizeLocaleKey(string $locale): string
    {
        return strtolower(str_replace('-', '_', $locale));
    }
}
