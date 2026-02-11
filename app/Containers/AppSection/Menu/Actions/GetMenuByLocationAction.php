<?php

namespace App\Containers\AppSection\Menu\Actions;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Supports\MenuCache;
use App\Containers\AppSection\Menu\Tasks\BuildMenuTreeTask;
use App\Containers\AppSection\Menu\Tasks\FindMenuByLocationTask;
use App\Containers\AppSection\Menu\UI\API\Requests\GetMenuByLocationRequest;
use App\Ship\Parents\Actions\Action as ParentAction;

final class GetMenuByLocationAction extends ParentAction
{
    public function __construct(
        private readonly FindMenuByLocationTask $findMenuByLocationTask,
        private readonly BuildMenuTreeTask $buildMenuTreeTask,
        private readonly MenuCache $menuCache,
    ) {
    }

    public function run(GetMenuByLocationRequest $request): Menu
    {
        $location = (string) $request->route('location');
        $locale = $request->query('lang_code');
        $normalizedLocale = is_string($locale) ? trim($locale) : null;

        if ($normalizedLocale !== null && $normalizedLocale !== '') {
            LanguageAdvancedManager::setTranslationLocale($normalizedLocale);
        }

        /** @var Menu $menu */
        $menu = $this->menuCache->rememberByLocation(
            $location,
            $normalizedLocale,
            function () use ($location): Menu {
                $menu = $this->findMenuByLocationTask->run($location, ['locations', 'nodes.translations'], true);
                $tree = $this->buildMenuTreeTask->run($menu->nodes);
                $menu->setRelation('nodes', $tree);

                return $menu;
            },
        );

        return $menu;
    }
}
