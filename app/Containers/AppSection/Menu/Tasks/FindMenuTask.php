<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindMenuTask extends ParentTask
{
    /**
     * @param array<int|string, mixed> $with
     */
    public function run(int $id, array $with = []): Menu
    {
        $with = $this->addNodeTranslations($with);

        return Menu::query()->with($with)->findOrFail($id);
    }

    /**
     * When 'nodes' or 'nodes.translations' is requested, auto-filter translations by locale.
     *
     * @param array<int|string, mixed> $with
     * @return array<int|string, mixed>
     */
    private function addNodeTranslations(array $with): array
    {
        // Find and remove 'nodes' or 'nodes.translations' if present as string value
        $nodesKey = array_search('nodes', $with, true);
        $nodesTransKey = array_search('nodes.translations', $with, true);

        $hasNodes = $nodesKey !== false || $nodesTransKey !== false;

        if (! $hasNodes) {
            return $with;
        }

        if ($nodesKey !== false) {
            unset($with[$nodesKey]);
        }
        if ($nodesTransKey !== false) {
            unset($with[$nodesTransKey]);
        }

        $langCode = LanguageAdvancedManager::getTranslationLocale();

        if ($langCode && ! LanguageAdvancedManager::isDefaultLocale($langCode)) {
            $with['nodes.translations'] = static fn ($query) => $query->where('lang_code', $langCode);
        } else {
            $with[] = 'nodes';
        }

        return $with;
    }
}
