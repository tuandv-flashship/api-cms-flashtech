<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindMenuNodeTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(int $id, array $with = []): MenuNode
    {
        return MenuNode::query()->with($with)->findOrFail($id);
    }
}
