<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Values\MemberRefreshToken;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie as CookieFacade;
use Symfony\Component\HttpFoundation\Cookie;

final class LogoutMemberAction extends ParentAction
{
    public function run(Member|null $member): Cookie
    {
        if ($member) {
            app(CreateMemberActivityLogTask::class)->run([
                'member_id' => $member->id,
                'action' => 'logout',
            ]);
        }

        $token = $member?->token();

        if (!$token) {
            return CookieFacade::forget(MemberRefreshToken::cookieName());
        }

        $tokenId = $token->getKey();
        $token->revoke();

        if (!$tokenId) {
            return CookieFacade::forget(MemberRefreshToken::cookieName());
        }

        DB::connection(config('passport.connection'))
            ->table('oauth_refresh_tokens')
            ->where('access_token_id', $tokenId)
            ->update(['revoked' => true]);

        return CookieFacade::forget(MemberRefreshToken::cookieName());
    }
}
