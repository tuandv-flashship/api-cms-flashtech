<?php

namespace App\Containers\AppSection\Tools\Importers;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Models\PageTranslation;
use App\Containers\AppSection\Tools\Supports\Concerns\TranslationLocaleHelper;
use App\Containers\AppSection\Tools\Supports\Import\ImportColumn;
use App\Containers\AppSection\Tools\Supports\Import\Importer;
use Illuminate\Support\Arr;

final class PageTranslationsImporter extends Importer
{
    use TranslationLocaleHelper;

    /**
     * @return array<int, ImportColumn>
     */
    public function columns(): array
    {
        $columns = [
            ImportColumn::make('id')
                ->label('ID')
                ->rules(['required', 'integer']),
            ImportColumn::make('name')
                ->label('Name')
                ->rules(['required', 'string', 'max:255']),
        ];

        $defaultLang = $this->getDefaultLangCode();
        foreach ($this->getSupportedLangCodes() as $langCode) {
            if ($langCode === $defaultLang) {
                continue;
            }

            $suffix = $this->normalizeLangKey($langCode);

            foreach ($this->getTranslatableColumnsFor(Page::class) as $column) {
                $columns[] = ImportColumn::make($column . '_' . $suffix)
                    ->label(ucfirst($column) . ' (' . $suffix . ')')
                    ->rules(['nullable', 'string', 'max:' . $this->maxLengthForColumn($column)]);
            }
        }

        return $columns;
    }

    public function chunkSize(): int
    {
        return (int) config('data-synchronize.imports.page_translations_chunk_size', 100);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function examples(): array
    {
        $pages = Page::query()->take(5)->get(['id', 'name']);
        if ($pages->isEmpty()) {
            return [
                ['id' => 1, 'name' => 'Example page'],
            ];
        }

        $translations = PageTranslation::query()
            ->whereIn('pages_id', $pages->pluck('id')->all())
            ->get()
            ->groupBy('pages_id');

        $rows = [];
        foreach ($pages as $page) {
            $row = [
                'id' => $page->getKey(),
                'name' => $page->name,
            ];

            foreach ($this->getSupportedLangCodes() as $langCode) {
                if ($langCode === $this->getDefaultLangCode()) {
                    continue;
                }

                $suffix = $this->normalizeLangKey($langCode);
                $translation = $translations->get($page->getKey(), collect())
                    ->firstWhere('lang_code', $langCode);

                foreach ($this->getTranslatableColumnsFor(Page::class) as $column) {
                    $row[$column . '_' . $suffix] = $translation?->{$column} ?? '';
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function handle(array $rows): int
    {
        $count = 0;
        $defaultLang = $this->getDefaultLangCode();
        $translatable = $this->getTranslatableColumnsFor(Page::class);

        // Batch pre-fetch all pages to eliminate N+1 queries.
        $pageIds = array_filter(array_map(
            static fn (array $row) => (int) Arr::get($row, 'id', 0),
            $rows
        ), static fn (int $id) => $id > 0);

        $pages = Page::query()
            ->whereIn('id', $pageIds)
            ->get()
            ->keyBy('id');

        foreach ($rows as $row) {
            $pageId = (int) Arr::get($row, 'id', 0);
            $page = $pages->get($pageId);
            if (! $page) {
                continue;
            }

            foreach ($this->getSupportedLangCodes() as $langCode) {
                if ($langCode === $defaultLang) {
                    continue;
                }

                $suffix = $this->normalizeLangKey($langCode);
                $translationData = [];

                foreach ($translatable as $column) {
                    $key = $column . '_' . $suffix;
                    if (array_key_exists($key, $row)) {
                        $translationData[$column] = $row[$key];
                    }
                }

                if ($translationData === []) {
                    continue;
                }

                LanguageAdvancedManager::saveTranslation($page, $translationData, $langCode);
                $count++;
            }
        }

        return $count;
    }
}
