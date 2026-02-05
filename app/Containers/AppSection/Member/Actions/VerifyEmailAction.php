<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\UI\API\Requests\VerifyEmailRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VerifyEmailAction extends ParentAction
{
    public function run(VerifyEmailRequest $request): void
    {
        if (!config('member.email_verification.enabled')) {
            throw new AuthorizationException('Email verification is disabled.');
        }

        if (!$request->hasValidSignature()) {
            throw new AuthorizationException('Invalid or expired URL.');
        }

        $id = $request->id;
        $hash = $request->hash;

        $member = Member::find($id);

        if (!$member) {
            throw new NotFoundHttpException('Member not found.');
        }

        if (!hash_equals((string) $hash, sha1($member->getEmailForVerification()))) {
             throw new AuthorizationException('Invalid hash.');
        }

        if (!$member->hasVerifiedEmail()) {
            $member->markEmailAsVerified();
            $member->status = MemberStatus::ACTIVE;
            $member->save();
        }
    }
}
