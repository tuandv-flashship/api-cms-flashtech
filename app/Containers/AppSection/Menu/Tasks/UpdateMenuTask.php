<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateMenuTask extends ParentTask
{
    public function __construct(
        private readonly FindMenuTask $findMenuTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(int $id, array $data): Menu
    {
        $menu = $this->findMenuTask->run($id);

        if ($data !== []) {
            $menu->fill($data);
            $menu->save();
        }

        return $menu;
    }
}
