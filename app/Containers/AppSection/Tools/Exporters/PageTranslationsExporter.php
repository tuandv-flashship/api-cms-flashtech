<?php

namespace App\Containers\AppSection\Tools\Exporters;

use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Models\PageTranslation;
use App\Containers\AppSection\Tools\Supports\Concerns\TranslationLocaleHelper;
use App\Containers\AppSection\Tools\Supports\Export\ExportColumn;
use App\Containers\AppSection\Tools\Supports\Export\Exporter;

final class PageTranslationsExporter extends Exporter
{
    use TranslationLocaleHelper;

    /**
     * @return array<int, ExportColumn>
     */
    public function columns(): array
    {
        $columns = [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Name'),
        ];

        foreach ($this->getLocaleMap() as $langCode => $suffix) {
            foreach ($this->getTranslatableColumnsFor(Page::class) as $column) {
                $label = ucfirst($column) . ' (' . $suffix . ')';
                $columns[] = ExportColumn::make($column . '_' . $suffix)->label($label);
            }
        }

        return $columns;
    }

    public function getTotal(): int
    {
        return Page::query()->count();
    }

    /**
     * @return iterable<int, array<string, mixed>>
     */
    protected function getRows(): iterable
    {
        $translatable = $this->getTranslatableColumnsFor(Page::class);
        $localeMap = $this->getLocaleMap();

        // Stream pages with cursor to avoid loading all into memory.
        foreach (Page::query()->select(['id', 'name'])->cursor() as $page) {
            $translations = PageTranslation::query()
                ->where('pages_id', $page->getKey())
                ->get()
                ->keyBy('lang_code');

            $row = [
                'id' => $page->getKey(),
                'name' => $page->name,
            ];

            foreach ($localeMap as $langCode => $suffix) {
                $translation = $translations->get($langCode);

                foreach ($translatable as $column) {
                    $row[$column . '_' . $suffix] = $translation?->{$column} ?? '';
                }
            }

            yield $row;
        }
    }
}
