<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Authentication\Values\UserCredential;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\CreateMemberTask;
use App\Containers\AppSection\Member\Tasks\FindMemberByEmailTask;
use App\Containers\AppSection\Member\Tasks\FindMemberSocialAccountByProviderTask;
use App\Containers\AppSection\Member\Tasks\UpdateOrCreateMemberSocialAccountTask;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

final class HandleSocialLoginCallbackAction extends ParentAction
{
    public function __construct(
        private readonly FindMemberByEmailTask $findMemberByEmailTask,
        private readonly FindMemberSocialAccountByProviderTask $findMemberSocialAccountByProviderTask,
        private readonly CreateMemberTask $createMemberTask,
        private readonly UpdateOrCreateMemberSocialAccountTask $updateOrCreateMemberSocialAccountTask,
        private readonly IssueMemberTokenAction $issueMemberTokenAction,
        private readonly CreateMemberActivityLogTask $createMemberActivityLogTask,
    ) {
    }

    public function run(
        string $provider,
        string|null $redirectUrl = null,
        string $clientType = MemberClientType::WEB,
    ): array
    {
        if (!config("member.social.{$provider}.enabled")) {
            throw new AuthorizationException('Social login provider is disabled.');
        }

        $driver = Socialite::driver($provider)->stateless();

        if ($redirectUrl) {
            $driver->redirectUrl($redirectUrl);
        }

        $socialUser = $driver->user();
        $providerId = $socialUser->getId();
        if (!$providerId) {
            throw new AuthorizationException('Social login provider id is missing.');
        }

        $socialAccount = $this->findMemberSocialAccountByProviderTask->run($provider, (string) $providerId);
        if ($socialAccount && $socialAccount->member) {
            $member = $socialAccount->member;
        } else {
            $email = $socialUser->getEmail();
            if (!$email) {
                throw new AuthorizationException('Social login email is missing.');
            }

            $member = $this->findMemberByEmailTask->run($email);

            if (!$member) {
                $usernameBase = Str::before($email, '@');

                $member = $this->createMemberTask->run([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'username' => Member::generateUniqueUsername($usernameBase),
                    'email' => $email,
                    'password' => Str::random(16),
                    'status' => MemberStatus::ACTIVE,
                    'email_verified_at' => now(),
                ]);
            }
        }

        $this->updateOrCreateMemberSocialAccountTask->run(
            [
                'member_id' => $member->id,
                'provider' => $provider,
            ],
            [
                'provider_id' => $providerId,
                'token' => $socialUser->token,
                'avatar' => $socialUser->getAvatar(),
            ],
        );

        $oneTimeToken = Str::random(64);
        $ttlMinutes = max(1, (int) config('member.social.one_time_token_ttl', 1));
        Cache::put($member->socialLoginTokenCacheKey(), Hash::make($oneTimeToken), now()->addMinutes($ttlMinutes));

        $tokenResult = $this->issueMemberTokenAction->run(
            UserCredential::create($member->email, $oneTimeToken),
            $clientType,
        );

        $this->createMemberActivityLogTask->run([
            'member_id' => $member->id,
            'action' => 'login',
        ]);

        return [
            'member' => $member,
            'token' => $tokenResult,
        ];
    }
}
