<?php

namespace App\Containers\AppSection\Menu\Actions;

use App\Containers\AppSection\Menu\Events\MenuSavedEvent;
use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Tasks\BuildMenuTreeTask;
use App\Containers\AppSection\Menu\Tasks\FindMenuTask;
use App\Containers\AppSection\Menu\Tasks\SaveMenuNodesTask;
use App\Containers\AppSection\Menu\Tasks\SyncMenuLocationsTask;
use App\Containers\AppSection\Menu\Tasks\UpdateMenuTask;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\UpdateMenuRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class UpdateMenuAction extends ParentAction
{
    public function __construct(
        private readonly UpdateMenuTask $updateMenuTask,
        private readonly SyncMenuLocationsTask $syncMenuLocationsTask,
        private readonly SaveMenuNodesTask $saveMenuNodesTask,
        private readonly FindMenuTask $findMenuTask,
        private readonly BuildMenuTreeTask $buildMenuTreeTask,
    ) {
    }

    public function run(UpdateMenuRequest $request): Menu
    {
        $id = (int) $request->id;
        $validated = $request->validated();

        DB::transaction(function () use ($id, $validated): void {
            $this->updateMenuTask->run($id, Arr::only($validated, ['name', 'slug', 'status']));

            if (array_key_exists('locations', $validated)) {
                $this->syncMenuLocationsTask->run($id, (array) $validated['locations']);
            }

            if (array_key_exists('nodes', $validated)) {
                $this->saveMenuNodesTask->run($id, (array) $validated['nodes']);
            }
        });

        MenuSavedEvent::dispatch($id);

        $menu = $this->findMenuTask->run($id, ['locations', 'nodes.translations']);
        $menu->setRelation('nodes', $this->buildMenuTreeTask->run($menu->nodes));

        return $menu;
    }
}
