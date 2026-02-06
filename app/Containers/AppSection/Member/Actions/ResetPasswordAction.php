<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\RevokeMemberTokensTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

final class ResetPasswordAction extends ParentAction
{
    public function __construct(
        private readonly RevokeMemberTokensTask $revokeMemberTokensTask,
    ) {
    }

    public function run(array $data): void
    {
        if (!config('member.password_reset.enabled')) {
            throw new AuthorizationException('Password reset is disabled.');
        }

        $status = Password::broker('members')->reset(
            $data,
            function (Member $member, string $password): void {
                $member->forceFill([
                    'password' => $password,
                ]);

                $member->save();

                $this->revokeMemberTokensTask->run($member);
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return;
        }

        if ($status === Password::INVALID_TOKEN) {
            throw ValidationException::withMessages(['token' => __($status)]);
        }

        throw ValidationException::withMessages(['email' => __($status)]);
    }
}
