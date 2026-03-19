<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Containers\AppSection\Translation\Tasks\GetTranslationGroupTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Cache;

final class GetTranslationGroupAction extends ParentAction
{
    public function __construct(
        private readonly GetTranslationGroupTask $getTranslationGroupTask,
        private readonly TranslationFilesystem $filesystem,
    ) {
    }

    /**
     * @return array{items: array<int, array{group: string, key: string, en: string|null, value: string|null}>, total: int, page: int, per_page: int, last_page: int}
     */
    public function run(string $locale, ?string $group = null, ?string $search = null, int $page = 1, int $perPage = 20): array
    {
        $rows = $this->loadTranslationRows($locale, $group);

        // Search filter
        if ($search !== null && $search !== '') {
            $searchLower = mb_strtolower($search);
            $rows = array_values(array_filter($rows, static function (array $row) use ($searchLower): bool {
                return str_contains(mb_strtolower($row['key']), $searchLower)
                    || ($row['en'] !== null && str_contains(mb_strtolower($row['en']), $searchLower))
                    || ($row['value'] !== null && str_contains(mb_strtolower($row['value']), $searchLower));
            }));
        }

        // Paginate
        $total = count($rows);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        return [
            'items' => array_slice($rows, $offset, $perPage),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ];
    }

    /**
     * @return array<int, array{group: string, key: string, en: string|null, value: string|null}>
     */
    private function loadTranslationRows(string $locale, ?string $group): array
    {
        $cacheKey = "translations.admin_rows.{$locale}." . ($group ?? '_all');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($locale, $group): array {
            $groups = $group !== null && $group !== ''
                ? [$group]
                : $this->filesystem->listGroups($locale);

            $rows = [];

            foreach ($groups as $g) {
                $enTranslations = $this->getTranslationGroupTask->run('en', $g);
                $localeTranslations = $locale !== 'en'
                    ? $this->getTranslationGroupTask->run($locale, $g)
                    : [];

                foreach ($enTranslations as $key => $enValue) {
                    $rows[] = [
                        'group' => $g,
                        'key' => $key,
                        'en' => $enValue,
                        'value' => $localeTranslations[$key] ?? null,
                    ];
                }

                foreach ($localeTranslations as $key => $value) {
                    if (! isset($enTranslations[$key])) {
                        $rows[] = [
                            'group' => $g,
                            'key' => $key,
                            'en' => null,
                            'value' => $value,
                        ];
                    }
                }
            }

            return $rows;
        });
    }
}
