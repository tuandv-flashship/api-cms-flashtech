<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Events\MemberLoggedIn;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\FindMemberByLoginTask;
use App\Containers\AppSection\Member\UI\API\Requests\LoginMemberRequest;
use App\Containers\AppSection\Member\Actions\IssueMemberTokenAction;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Containers\AppSection\Authentication\Values\UserCredential;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

final class LoginMemberAction extends ParentAction
{
    public function __construct(
        private readonly FindMemberByLoginTask $findMemberByLoginTask,
        private readonly IssueMemberTokenAction $issueMemberTokenAction,
        private readonly CreateMemberActivityLogTask $createMemberActivityLogTask,
    ) {
    }

    public function run(LoginMemberRequest $request, string $clientType = MemberClientType::WEB): array
    {
        $input = $request->validated();

        if (!config('member.auth.login_enabled', true)) {
            throw new AuthorizationException('Member login is disabled.');
        }

        $login = (string) $input['login'];

        $member = $this->findMemberByLoginTask->run($login);

        if (!$member || !Hash::check($input['password'], $member->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (config('member.email_verification.enabled') && !$member->hasVerifiedEmail()) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if ($member->status !== MemberStatus::ACTIVE) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $token = $this->issueMemberTokenAction->run(
            UserCredential::create($login, $input['password']),
            $clientType,
        );

        MemberLoggedIn::dispatch($member);
        $this->createMemberActivityLogTask->run([
            'member_id' => $member->id,
            'action' => 'login',
        ]);

        return [
            'member' => $member,
            'token' => $token,
        ];
    }
}
