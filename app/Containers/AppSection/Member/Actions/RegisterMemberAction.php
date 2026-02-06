<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Events\MemberRegistered;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\CreateMemberTask;
use App\Containers\AppSection\Member\UI\API\Requests\RegisterMemberRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

final class RegisterMemberAction extends ParentAction
{
    public function __construct(
        private readonly CreateMemberTask $createMemberTask,
    ) {
    }

    public function run(RegisterMemberRequest $request): Member
    {
        if (!config('member.auth.registration_enabled', true)) {
            throw new AuthorizationException('Member registration is disabled.');
        }

        $data = $request->validated();
        if (empty($data['username'])) {
            $base = !empty($data['email'])
                ? Str::before($data['email'], '@')
                : ($data['name'] ?? 'member');
            $data['username'] = Member::generateUniqueUsername($base);
        }

        $isEmailVerificationEnabled = (bool) config('member.email_verification.enabled');
        $data['status'] = $isEmailVerificationEnabled ? MemberStatus::PENDING : MemberStatus::ACTIVE;
        if (!$isEmailVerificationEnabled) {
            $data['email_verified_at'] = now();
        }

        $member = $this->createMemberTask->run($data);

        MemberRegistered::dispatch($member);

        return $member;
    }
}
