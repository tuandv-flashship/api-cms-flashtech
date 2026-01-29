<?php

namespace App\Containers\AppSection\Tools\Importers;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\PostTranslation;
use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Tools\Supports\Import\ImportColumn;
use App\Containers\AppSection\Tools\Supports\Import\Importer;
use Illuminate\Support\Arr;

final class PostTranslationsImporter extends Importer
{
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

            foreach ($this->getTranslatableColumns() as $column) {
                $columns[] = ImportColumn::make($column . '_' . $suffix)
                    ->label(ucfirst($column) . ' (' . $suffix . ')')
                    ->rules(['nullable', 'string', 'max:' . $this->maxLengthForColumn($column)]);
            }
        }

        return $columns;
    }

    public function chunkSize(): int
    {
        return (int) config('data-synchronize.imports.post_translations_chunk_size', 100);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function examples(): array
    {
        $posts = Post::query()->take(5)->get(['id', 'name']);
        if ($posts->isEmpty()) {
            return [
                ['id' => 1, 'name' => 'Example post'],
            ];
        }

        $translations = PostTranslation::query()
            ->whereIn('posts_id', $posts->pluck('id')->all())
            ->get()
            ->groupBy('posts_id');

        $rows = [];
        foreach ($posts as $post) {
            $row = [
                'id' => $post->getKey(),
                'name' => $post->name,
            ];

            foreach ($this->getSupportedLangCodes() as $langCode) {
                if ($langCode === $this->getDefaultLangCode()) {
                    continue;
                }

                $suffix = $this->normalizeLangKey($langCode);
                $translation = $translations->get($post->getKey(), collect())
                    ->firstWhere('lang_code', $langCode);

                foreach ($this->getTranslatableColumns() as $column) {
                    $row[$column . '_' . $suffix] = $translation->{$column} ?? '';
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
        $translatable = $this->getTranslatableColumns();

        foreach ($rows as $row) {
            $postId = (int) Arr::get($row, 'id', 0);
            if ($postId <= 0) {
                continue;
            }

            $post = Post::query()->find($postId);
            if (! $post) {
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

                LanguageAdvancedManager::saveTranslation($post, $translationData, $langCode);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<int, string>
     */
    private function getSupportedLangCodes(): array
    {
        $codes = Language::query()
            ->orderBy('lang_order')
            ->pluck('lang_code')
            ->filter()
            ->all();

        return $codes !== [] ? $codes : [config('app.locale', 'en')];
    }

    private function getDefaultLangCode(): ?string
    {
        return LanguageAdvancedManager::getDefaultLocaleCode() ?: config('app.locale');
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

    private function maxLengthForColumn(string $column): int
    {
        return match ($column) {
            'description' => 400,
            'content' => 300000,
            default => 255,
        };
    }
}
