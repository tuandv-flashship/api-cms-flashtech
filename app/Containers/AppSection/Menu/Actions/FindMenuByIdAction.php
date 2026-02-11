<?php

namespace App\Containers\AppSection\Menu\Actions;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Tasks\BuildMenuTreeTask;
use App\Containers\AppSection\Menu\Tasks\FindMenuTask;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\FindMenuByIdRequest;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindMenuByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindMenuTask $findMenuTask,
        private readonly BuildMenuTreeTask $buildMenuTreeTask,
    ) {
    }

    public function run(FindMenuByIdRequest $request): Menu
    {
        $menu = $this->findMenuTask->run((int) $request->id, ['locations', 'nodes.translations']);
        $tree = $this->buildMenuTreeTask->run($menu->nodes);

        $menu->setRelation('nodes', $tree);

        return $menu;
    }
}
