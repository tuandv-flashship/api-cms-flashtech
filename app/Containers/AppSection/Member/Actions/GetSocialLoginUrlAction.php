<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Socialite\Facades\Socialite;

class GetSocialLoginUrlAction extends ParentAction
{
    public function run(string $provider, string|null $redirectUrl = null): string
    {
        if (!config("member.social.{$provider}.enabled")) {
            throw new AuthorizationException('Social login provider is disabled.');
        }

        $driver = Socialite::driver($provider)->stateless();

        if ($redirectUrl) {
            $driver->redirectUrl($redirectUrl);
        }

        return $driver->redirect()->getTargetUrl();
    }
}
