<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindMenuByLocationTask extends ParentTask
{
    /**
     * @param array<int|string, mixed> $with
     */
    public function run(string $location, array $with = [], bool $publishedOnly = true): Menu
    {
        $with = $this->addNodeTranslations($with);

        $query = Menu::query()
            ->with($with)
            ->whereHas('locations', static fn ($locationQuery) => $locationQuery->where('location', $location));

        if ($publishedOnly) {
            $query->published();
        }

        return $query->firstOrFail();
    }

    /**
     * @param array<int|string, mixed> $with
     * @return array<int|string, mixed>
     */
    private function addNodeTranslations(array $with): array
    {
        $langCode = LanguageAdvancedManager::getTranslationLocale();

        if ($langCode && ! LanguageAdvancedManager::isDefaultLocale($langCode)) {
            $with['nodes.translations'] = static fn ($query) => $query->where('lang_code', $langCode);
        } else {
            // Ensure nodes are loaded even for default locale
            if (! in_array('nodes', $with, true) && ! isset($with['nodes'])) {
                $with[] = 'nodes';
            }
        }

        return $with;
    }
}
