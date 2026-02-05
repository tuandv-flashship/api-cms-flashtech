<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\RevokeMemberTokensTask;

class ResetPasswordAction extends ParentAction
{
    public function run(array $data): void
    {
        if (!config('member.password_reset.enabled')) {
            throw new AuthorizationException('Password reset is disabled.');
        }

        $status = Password::broker('members')->reset(
            $data,
            static function (Member $member, string $password): void {
                $member->forceFill([
                    'password' => $password,
                ]);

                $member->save();

                app(RevokeMemberTokensTask::class)->run($member);
            }
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
