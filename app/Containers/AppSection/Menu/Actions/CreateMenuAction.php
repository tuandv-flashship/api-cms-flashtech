<?php

namespace App\Containers\AppSection\Menu\Actions;

use App\Containers\AppSection\Menu\Events\MenuSavedEvent;
use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Tasks\BuildMenuTreeTask;
use App\Containers\AppSection\Menu\Tasks\CreateMenuTask;
use App\Containers\AppSection\Menu\Tasks\FindMenuTask;
use App\Containers\AppSection\Menu\Tasks\SaveMenuNodesTask;
use App\Containers\AppSection\Menu\Tasks\SyncMenuLocationsTask;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\CreateMenuRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class CreateMenuAction extends ParentAction
{
    public function __construct(
        private readonly CreateMenuTask $createMenuTask,
        private readonly SyncMenuLocationsTask $syncMenuLocationsTask,
        private readonly SaveMenuNodesTask $saveMenuNodesTask,
        private readonly FindMenuTask $findMenuTask,
        private readonly BuildMenuTreeTask $buildMenuTreeTask,
    ) {
    }

    public function run(CreateMenuRequest $request): Menu
    {
        $validated = $request->validated();

        /** @var Menu $menu */
        $menu = DB::transaction(function () use ($validated): Menu {
            $menu = $this->createMenuTask->run(Arr::only($validated, ['name', 'slug', 'status']));

            $this->syncMenuLocationsTask->run((int) $menu->getKey(), (array) ($validated['locations'] ?? []));
            $this->saveMenuNodesTask->run((int) $menu->getKey(), (array) ($validated['nodes'] ?? []));

            return $menu;
        });

        MenuSavedEvent::dispatch((int) $menu->getKey());

        $menu = $this->findMenuTask->run((int) $menu->getKey(), ['locations', 'nodes.translations']);
        $menu->setRelation('nodes', $this->buildMenuTreeTask->run($menu->nodes));

        return $menu;
    }
}
