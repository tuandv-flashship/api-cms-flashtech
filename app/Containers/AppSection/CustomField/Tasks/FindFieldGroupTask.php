<?php

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindFieldGroupTask extends ParentTask
{
    public function run(int $id): FieldGroup
    {
        return FieldGroup::query()->findOrFail($id);
    }
}
