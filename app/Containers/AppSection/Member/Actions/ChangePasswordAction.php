<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\UI\API\Requests\ChangePasswordRequest;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\RevokeMemberTokensTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Ship\Exceptions\IncorrectPasswordException;

class ChangePasswordAction extends ParentAction
{
    public function run(ChangePasswordRequest $request): void
    {
        $member = Auth::guard('member')->user();
        $input = $request->validated();

        if (!Hash::check($input['current_password'], $member->password)) {
             throw new IncorrectPasswordException(); 
        }

        $member->password = Hash::make($input['new_password']);
        $member->save();

        app(RevokeMemberTokensTask::class)->run($member);

        app(CreateMemberActivityLogTask::class)->run([
            'member_id' => $member->id,
            'action' => 'update_security',
        ]);
    }
}
