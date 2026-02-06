<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\FindMemberByIdTask;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\FindMemberByIdRequest;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindMemberByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindMemberByIdTask $findMemberByIdTask,
    ) {
    }

    public function run(FindMemberByIdRequest $request): Member
    {
        return $this->findMemberByIdTask->run($request->id);
    }
}
