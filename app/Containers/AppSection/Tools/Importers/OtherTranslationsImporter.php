<?php

namespace App\Containers\AppSection\Tools\Importers;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Tools\Supports\Import\ImportColumn;
use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use Illuminate\Support\Arr;

final class OtherTranslationsImporter extends Importer
{
    public function __construct(
        TranslationFilesystem $filesystem,
        \App\Containers\AppSection\Tools\Supports\SpreadsheetReader $reader,
        \App\Containers\AppSection\Tools\Supports\DataSynchronizeStorage $storage
    ) {
        parent::__construct($reader, $storage);
        $this->filesystem = $filesystem;
    }

    private TranslationFilesystem $filesystem;

    /**
     * @return array<int, ImportColumn>
     */
    public function columns(): array
    {
        $columns = [
            ImportColumn::make('key')
                ->label('key')
                ->rules(['required', 'string']),
        ];

        foreach ($this->getLocaleMappings() as $key => $locale) {
            $columns[] = ImportColumn::make($key)
                ->label($locale)
                ->rules(['nullable', 'string', 'max:10000']);
        }

        return $columns;
    }

    public function chunkSize(): int
    {
        return (int) config('data-synchronize.imports.other_translations_chunk_size', 1000);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function examples(): array
    {
        return [
            [
                'key' => 'actions::accept',
                'en' => 'Accept',
                'vi' => 'Chấp nhận',
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function handle(array $rows): int
    {
        $localeMappings = $this->getLocaleMappings();
        $payload = [];
        $count = 0;

        foreach ($rows as $row) {
            $rawKey = trim((string) Arr::get($row, 'key', ''));
            if ($rawKey === '' || ! str_contains($rawKey, '::')) {
                continue;
            }

            [$group, $key] = explode('::', $rawKey, 2);
            $group = trim($group);
            $key = trim($key);

            if ($group === '' || $key === '') {
                continue;
            }

            foreach ($localeMappings as $columnKey => $locale) {
                if (! array_key_exists($columnKey, $row)) {
                    continue;
                }

                $payload[$locale][$group][$key] = $row[$columnKey];
            }
        }

        foreach ($payload as $locale => $groups) {
            foreach ($groups as $group => $translations) {
                $this->filesystem->writeTranslations($locale, $group, $translations);
                $count += count($translations);
            }
        }

        return $count;
    }

    /**
     * @return array<string, string>
     */
    private function getLocaleMappings(): array
    {
        $locales = Language::query()
            ->orderBy('lang_order')
            ->get(['lang_locale', 'lang_code'])
            ->map(function (Language $language): string {
                return $language->lang_locale ?: $language->lang_code ?: '';
            })
            ->filter()
            ->values()
            ->all();

        if ($locales === []) {
            $locales = [config('app.locale', 'en')];
        }

        $mapping = [];
        foreach ($locales as $locale) {
            $mapping[$this->normalizeLocaleKey($locale)] = $locale;
        }

        return $mapping;
    }

    private function normalizeLocaleKey(string $locale): string
    {
        return strtolower(str_replace('-', '_', $locale));
    }
}
