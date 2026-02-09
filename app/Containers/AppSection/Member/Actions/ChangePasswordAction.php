<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberActivityAction;
use App\Containers\AppSection\Member\UI\API\Requests\ChangePasswordRequest;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\RevokeMemberTokensTask;
use App\Containers\AppSection\Member\Tasks\UpdateMemberTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Ship\Exceptions\IncorrectPasswordException;

final class ChangePasswordAction extends ParentAction
{
    public function __construct(
        private readonly UpdateMemberTask $updateMemberTask,
        private readonly RevokeMemberTokensTask $revokeMemberTokensTask,
        private readonly CreateMemberActivityLogTask $createMemberActivityLogTask,
    ) {
    }

    public function run(ChangePasswordRequest $request): void
    {
        $member = Auth::guard('member')->user();
        $input = $request->validated();

        if (!Hash::check($input['current_password'], $member->password)) {
            throw new IncorrectPasswordException();
        }

        $this->updateMemberTask->run($member->id, [
            'password' => $input['new_password'],
        ]);

        $this->revokeMemberTokensTask->run($member);

        $this->createMemberActivityLogTask->run([
            'member_id' => $member->id,
            'action' => MemberActivityAction::UPDATE_SECURITY->value,
        ]);
    }
}
