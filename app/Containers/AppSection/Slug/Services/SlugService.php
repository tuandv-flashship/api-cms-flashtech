<?php

namespace App\Containers\AppSection\Slug\Services;

use App\Containers\AppSection\LanguageAdvanced\Models\SlugTranslation;
use App\Containers\AppSection\Slug\Models\Slug;
use Illuminate\Support\Str;

final class SlugService
{
    public function create(
        string $name,
        int|string|null $slugId = null,
        ?string $prefix = null,
        bool $translateToLatin = true,
        ?string $langCode = null
    ): string {
        $language = $translateToLatin ? 'en' : false;
        $slug = Str::slug($name, '-', $language);

        $index = 1;
        $baseSlug = $slug;

        while ($this->exists($slug, $slugId, $prefix, $langCode)) {
            $slug = $baseSlug . '-' . $index++;
        }

        if ($slug === '') {
            $slug = (string) time();
        }

        return $slug;
    }

    private function exists(?string $slug, int|string|null $slugId, ?string $prefix, ?string $langCode): bool
    {
        $query = Slug::query()
            ->where('key', $slug)
            ->where('prefix', $prefix ?? '');

        if ($slugId) {
            $query->where('id', '!=', $slugId);
        }

        if ($query->exists()) {
            return true;
        }

        if ($langCode) {
            $translationQuery = SlugTranslation::query()
                ->where('lang_code', $langCode)
                ->where('key', $slug)
                ->where('prefix', $prefix ?? '');

            if ($slugId) {
                $translationQuery->where('slugs_id', '!=', $slugId);
            }

            return $translationQuery->exists();
        }

        return false;
    }
}
