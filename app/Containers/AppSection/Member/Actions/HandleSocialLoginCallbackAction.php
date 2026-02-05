<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Models\MemberSocialAccount;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Actions\IssueMemberTokenAction;
use App\Containers\AppSection\Authentication\Values\UserCredential;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class HandleSocialLoginCallbackAction extends ParentAction
{
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
        $email = $socialUser->getEmail();

        if (!$email) {
            throw new AuthorizationException('Social login email is missing.');
        }

        $member = Member::where('email', $email)->first();

        if (!$member) {
            $usernameBase = Str::before($email, '@');

            $member = Member::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'username' => Member::generateUniqueUsername($usernameBase),
                'email' => $email,
                'password' => Hash::make(Str::random(16)), // Random password
                'status' => MemberStatus::ACTIVE,
                'email_verified_at' => now(),
            ]);
        }

        // Link social account if not exists
        if (!$member->socialAccounts()->where('provider', $provider)->exists()) {
            MemberSocialAccount::create([
                'member_id' => $member->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'token' => $socialUser->token,
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        $oneTimeToken = Str::random(64);
        $ttlMinutes = max(1, (int) config('member.social.one_time_token_ttl', 1));
        Cache::put($member->socialLoginTokenCacheKey(), Hash::make($oneTimeToken), now()->addMinutes($ttlMinutes));

        $tokenResult = app(IssueMemberTokenAction::class)->run(
            UserCredential::create($member->email, $oneTimeToken),
            $clientType,
        );

        app(CreateMemberActivityLogTask::class)->run([
            'member_id' => $member->id,
            'action' => 'login',
        ]);

        return [
            'member' => $member,
            'token' => $tokenResult,
        ];
    }
}
