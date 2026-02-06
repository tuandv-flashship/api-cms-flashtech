<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Tasks\FindMemberByIdTask;
use App\Containers\AppSection\Member\Tasks\UpdateMemberTask;
use App\Containers\AppSection\Member\UI\API\Requests\VerifyEmailRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;

final class VerifyEmailAction extends ParentAction
{
    public function __construct(
        private readonly FindMemberByIdTask $findMemberByIdTask,
        private readonly UpdateMemberTask $updateMemberTask,
    ) {
    }

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

        $member = $this->findMemberByIdTask->run($id);

        if (!hash_equals((string) $hash, sha1($member->getEmailForVerification()))) {
             throw new AuthorizationException('Invalid hash.');
        }

        if (!$member->hasVerifiedEmail()) {
            $member->markEmailAsVerified();
            $this->updateMemberTask->run($member->id, [
                'status' => MemberStatus::ACTIVE,
            ]);
        }
    }
}
