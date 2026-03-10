<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Models\Translation;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ListTranslationGroupsTask extends ParentTask
{
    /**
     * @return array<int, string>
     */
    public function run(string $locale): array
    {
        $groups = Translation::query()
            ->where('locale', $locale)
            ->distinct()
            ->pluck('group_key')
            ->sort()
            ->values()
            ->toArray();

        // Replace wildcard '*' (JSON translations) with 'json' for readability.
        return array_map(
            static fn (string $g): string => $g === '*' ? 'json' : $g,
            $groups,
        );
    }
}

