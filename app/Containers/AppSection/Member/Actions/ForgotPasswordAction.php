<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordAction extends ParentAction
{
    public function run(string $email): void
    {
        if (!config('member.password_reset.enabled')) {
            return;
        }

        $status = Password::broker('members')->sendResetLink([
            'email' => $email,
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return;
        }

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages(['throttle' => __($status)]);
        }

        throw ValidationException::withMessages(['email' => __($status)]);
    }
}
