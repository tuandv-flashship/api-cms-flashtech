<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindMenuTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(int $id, array $with = []): Menu
    {
        return Menu::query()->with($with)->findOrFail($id);
    }
}
