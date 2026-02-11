<?php

namespace App\Containers\AppSection\Menu\Actions;

use App\Containers\AppSection\Menu\Events\MenuDeletedEvent;
use App\Containers\AppSection\Menu\Tasks\DeleteMenuTask;
use App\Containers\AppSection\Menu\Tasks\FindMenuTask;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\DeleteMenuRequest;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteMenuAction extends ParentAction
{
    public function __construct(
        private readonly FindMenuTask $findMenuTask,
        private readonly DeleteMenuTask $deleteMenuTask,
    ) {
    }

    public function run(DeleteMenuRequest $request): bool
    {
        $menu = $this->findMenuTask->run((int) $request->id, ['locations']);
        $locations = $menu->locations
            ->pluck('location')
            ->filter(fn (mixed $value) => is_string($value) && $value !== '')
            ->values()
            ->all();
        $deleted = $this->deleteMenuTask->run((int) $menu->getKey());

        if ($deleted) {
            MenuDeletedEvent::dispatch((int) $menu->getKey(), $locations);
        }

        return $deleted;
    }
}
