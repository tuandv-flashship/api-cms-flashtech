<?php

namespace App\Containers\AppSection\Translation\Supports;

use App\Containers\AppSection\Translation\Models\Translation;
use Illuminate\Database\QueryException;
use Illuminate\Translation\FileLoader;

/**
 * Extends Laravel's FileLoader to merge DB translations over file translations.
 *
 * Priority: DB > File (DB overrides file for matching keys).
 * If the translations table doesn't exist yet, falls back to file-only.
 */
final class TranslationLoaderManager extends FileLoader
{
    /**
     * @param  string       $locale
     * @param  string       $group
     * @param  string|null  $namespace
     * @return array<string, mixed>
     */
    public function load($locale, $group, $namespace = null): array
    {
        $fileTranslations = parent::load($locale, $group, $namespace);

        // Only merge DB translations for the default namespace
        if ($namespace !== null && $namespace !== '*') {
            return $fileTranslations;
        }

        try {
            $dbTranslations = Translation::getForGroup($locale, $group);

            return array_replace_recursive($fileTranslations, $dbTranslations);
        } catch (QueryException) {
            // Table may not exist yet (pre-migration)
            return $fileTranslations;
        }
    }
}
