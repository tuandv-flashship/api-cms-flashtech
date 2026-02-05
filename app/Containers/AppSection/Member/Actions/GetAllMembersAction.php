<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Tasks\GetAllMembersTask;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\GetAllMembersRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Apiato\Core\Page\Page;

class GetAllMembersAction extends ParentAction
{
    public function run(GetAllMembersRequest $request): mixed
    {
        return app(GetAllMembersTask::class)->run();
    }
}
