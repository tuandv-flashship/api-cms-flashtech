<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Events\MemberRegistered;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\CreateMemberTask;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\CreateMemberRequest;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateMemberAction extends ParentAction
{
    public function __construct(
        private readonly CreateMemberTask $createMemberTask,
    ) {
    }

    public function run(CreateMemberRequest $request): Member
    {
        $data = $request->validated();

        if (empty($data['username'])) {
            $base = $data['email'] ?? $data['name'] ?? 'member';
            $data['username'] = Member::generateUniqueUsername($base);
        }

        $emailVerificationEnabled = (bool) config('member.email_verification.enabled');
        $emailVerified = (bool) ($data['email_verified'] ?? false);
        $sendVerification = (bool) ($data['send_verification'] ?? $emailVerificationEnabled);
        $statusInput = $data['status'] ?? null;
        $status = $statusInput instanceof MemberStatus
            ? $statusInput
            : ($statusInput ? MemberStatus::from($statusInput) : null);

        unset($data['email_verified'], $data['send_verification']);

        if ($emailVerificationEnabled) {
            if ($emailVerified) {
                $data['email_verified_at'] = now();
                $data['status'] = MemberStatus::ACTIVE;
            } else {
                // If email verification is enabled, only allow inactive or pending until verified.
                if ($status === MemberStatus::INACTIVE) {
                    $data['status'] = MemberStatus::INACTIVE;
                } else {
                    $data['status'] = MemberStatus::PENDING;
                }
            }
        } else {
            $data['email_verified_at'] = now();
            $data['status'] = $status ?? MemberStatus::ACTIVE;
        }

        $member = $this->createMemberTask->run($data);

        if ($emailVerificationEnabled && $sendVerification && !$member->hasVerifiedEmail()) {
            MemberRegistered::dispatch($member);
        }

        return $member;
    }
}
