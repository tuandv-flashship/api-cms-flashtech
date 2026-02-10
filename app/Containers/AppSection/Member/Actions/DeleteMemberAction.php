<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberActivityAction;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\FindMemberByIdTask;
use App\Containers\AppSection\Member\Tasks\DeleteMemberTask;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\DeleteMemberRequest;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteMemberAction extends ParentAction
{
    public function __construct(
        private readonly FindMemberByIdTask $findMemberByIdTask,
        private readonly CreateMemberActivityLogTask $createMemberActivityLogTask,
        private readonly DeleteMemberTask $deleteMemberTask,
    ) {
    }

    public function run(DeleteMemberRequest $request): void
    {
        $member = $this->findMemberByIdTask->run($request->id);

        $this->createMemberActivityLogTask->run([
            'member_id' => $member->id,
            'action' => MemberActivityAction::DELETE->value,
            'user_agent' => $request->userAgent(),
            'reference_url' => $request->fullUrl(),
            'reference_name' => $member->name,
            'ip_address' => $request->ip(),
        ]);

        $this->deleteMemberTask->run($member->id);
    }
}
