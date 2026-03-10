<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ListAdminMenuSectionsAction extends ParentAction
{
    /**
     * Get distinct sections grouped by parent admin menu item.
     * Only returns parent menus that have children with non-null sections.
     *
     * @return array<int, array{id: string, title: string, sections: array<int, string>}>
     */
    public function run(): array
    {
        $with = LanguageAdvancedManager::withTranslations([], AdminMenuItem::class);

        // Load parents that use panel display (Settings, System, etc.)
        $parents = AdminMenuItem::query()
            ->whereNull('parent_id')
            ->where('children_display', 'panel')
            ->where('is_active', true)
            ->with($with)
            ->orderBy('priority')
            ->get();

        // Batch-load children with sections in one query
        $parentIds = $parents->pluck('id')->all();

        $children = AdminMenuItem::query()
            ->whereIn('parent_id', $parentIds)
            ->whereNotNull('section')
            ->where('section', '!=', '')
            ->where('is_active', true)
            ->with($with)
            ->orderBy('priority')
            ->get();

        $result = [];

        foreach ($parents as $parent) {
            $parentChildren = $children->where('parent_id', $parent->getKey());

            // Get distinct translated section names, preserving order
            $sections = $parentChildren
                ->pluck('section')
                ->unique()
                ->values()
                ->all();

            if ($sections === []) {
                continue;
            }

            $result[] = [
                'id' => $parent->key,
                'title' => $parent->name, // translated via HasLanguageTranslations
                'sections' => $sections,
            ];
        }

        return $result;
    }
}
