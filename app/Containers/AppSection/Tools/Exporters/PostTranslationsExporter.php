<?php

namespace App\Containers\AppSection\Tools\Exporters;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\PostTranslation;
use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Tools\Supports\Export\ExportColumn;
use App\Containers\AppSection\Tools\Supports\Export\Exporter;

final class PostTranslationsExporter extends Exporter
{
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
            foreach ($this->getTranslatableColumns() as $column) {
                $label = ucfirst($column) . ' (' . $suffix . ')';
                $columns[] = ExportColumn::make($column . '_' . $suffix)->label($label);
            }
        }

        return $columns;
    }

    /**
     * @return iterable<int, array<string, mixed>>
     */
    protected function getRows(): iterable
    {
        $posts = Post::query()->select(['id', 'name'])->get();
        if ($posts->isEmpty()) {
            return [];
        }

        $translations = PostTranslation::query()
            ->whereIn('posts_id', $posts->pluck('id')->all())
            ->get()
            ->groupBy('posts_id');

        $translatable = $this->getTranslatableColumns();

        foreach ($posts as $post) {
            $row = [
                'id' => $post->getKey(),
                'name' => $post->name,
            ];

            foreach ($this->getLocaleMap() as $langCode => $suffix) {
                $translation = $translations->get($post->getKey(), collect())
                    ->firstWhere('lang_code', $langCode);

                foreach ($translatable as $column) {
                    $row[$column . '_' . $suffix] = $translation->{$column} ?? '';
                }
            }

            yield $row;
        }
    }

    /**
     * @return array<string, string>
     */
    private function getLocaleMap(): array
    {
        $default = LanguageAdvancedManager::getDefaultLocaleCode();
        $locales = Language::query()
            ->orderBy('lang_order')
            ->pluck('lang_code')
            ->filter()
            ->all();

        if ($locales === []) {
            $locales = [config('app.locale', 'en')];
        }

        $map = [];
        foreach ($locales as $langCode) {
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
    private function getTranslatableColumns(): array
    {
        return LanguageAdvancedManager::getTranslatableColumns(Post::class);
    }

    private function normalizeLangKey(string $langCode): string
    {
        return strtolower(str_replace('-', '_', $langCode));
    }
}
