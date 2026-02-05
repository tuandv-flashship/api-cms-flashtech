<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Notifications\VerifyEmailNotification;
use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SendVerificationEmailAction extends ParentAction
{
    public function run(Member $member): void
    {
        if (!Config::get('member.email_verification.enabled')) {
            return;
        }

        if ($member->hasVerifiedEmail()) {
            return;
        }

        $verificationUrl = URL::temporarySignedRoute(
            'api_member_verify_email',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $member->getHashedKey(),
                'hash' => sha1($member->getEmailForVerification()),
            ]
        );

        $member->notify(new VerifyEmailNotification($verificationUrl));
    }
}
