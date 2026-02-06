<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Tasks\GetAllMembersTask;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\GetAllMembersRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetAllMembersAction extends ParentAction
{
    public function __construct(
        private readonly GetAllMembersTask $getAllMembersTask,
    ) {
    }

    public function run(GetAllMembersRequest $request): LengthAwarePaginator
    {
        return $this->getAllMembersTask->run();
    }
}
