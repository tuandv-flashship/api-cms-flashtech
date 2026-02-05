<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Events\MemberLoggedIn;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\UI\API\Requests\LoginMemberRequest;
use App\Containers\AppSection\Member\Actions\IssueMemberTokenAction;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Containers\AppSection\Authentication\Values\UserCredential;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoginMemberAction extends ParentAction
{
    public function run(LoginMemberRequest $request, string $clientType = MemberClientType::WEB): array
    {
        $input = $request->validated();

        if (!config('member.auth.login_enabled', true)) {
            throw new AuthorizationException('Member login is disabled.');
        }

        $login = strtolower($input['login']);

        $member = Member::query()
            ->where('username', $login)
            ->first();

        if (!$member) {
            $member = Member::where('email', $login)->first();
        }

        if (!$member) {
            throw new NotFoundHttpException('Member not found.');
        }

        if (!Hash::check($input['password'], $member->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (config('member.email_verification.enabled') && !$member->hasVerifiedEmail()) {
            throw new AuthenticationException('Email is not verified.');
        }

        if ($member->status !== MemberStatus::ACTIVE) {
            throw new AuthenticationException('Member account is not active.');
        }

        $token = app(IssueMemberTokenAction::class)->run(
            UserCredential::create($login, $input['password']),
            $clientType,
        );

        MemberLoggedIn::dispatch($member);
        app(CreateMemberActivityLogTask::class)->run([
            'member_id' => $member->id,
            'action' => 'login',
        ]);

        return [
            'member' => $member,
            'token' => $token,
        ];
    }
}
