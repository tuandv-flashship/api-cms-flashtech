<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Models\Translation;
use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class UpdateTranslationGroupTask extends ParentTask
{
    public function __construct(private readonly TranslationFilesystem $filesystem)
    {
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<string, string>
     */
    public function run(string $locale, string $group, array $translations): array
    {
        // 1. Write to file (existing behavior)
        $result = $this->filesystem->writeTranslations($locale, $group, $translations);

        // 2. Also upsert to DB for FE API
        $this->upsertToDatabase($locale, $group, $translations);

        return $result;
    }

    /**
     * @param array<string, mixed> $translations
     */
    private function upsertToDatabase(string $locale, string $group, array $translations): void
    {
        // Normalize: 'json' group → '*' in DB (consistent with import convention)
        $dbGroup = $group === 'json' ? '*' : $group;
        $flattened = Arr::dot($translations);
        $rows = [];
        $now = now();

        foreach ($flattened as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $rows[] = [
                'locale' => $locale,
                'group_key' => $dbGroup,
                'item_key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows === []) {
            return;
        }

        DB::table('translations')->upsert(
            $rows,
            ['locale', 'group_key', 'item_key'],
            ['value', 'updated_at'],
        );

        Translation::flushLocaleCache($locale);
    }
}
