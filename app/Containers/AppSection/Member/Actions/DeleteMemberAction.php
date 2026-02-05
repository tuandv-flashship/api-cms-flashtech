<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\FindMemberByIdTask;
use App\Containers\AppSection\Member\Tasks\DeleteMemberTask;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\DeleteMemberRequest;
use App\Ship\Parents\Actions\Action as ParentAction;

class DeleteMemberAction extends ParentAction
{
    public function run(DeleteMemberRequest $request): void
    {
        $member = app(FindMemberByIdTask::class)->run($request->id);

        app(CreateMemberActivityLogTask::class)->run([
            'member_id' => $member->id,
            'action' => 'delete',
            'user_agent' => $request->userAgent(),
            'reference_url' => $request->fullUrl(),
            'reference_name' => $member->name,
            'ip_address' => $request->ip(),
        ]);

        app(DeleteMemberTask::class)->run($member->id);
    }
}
