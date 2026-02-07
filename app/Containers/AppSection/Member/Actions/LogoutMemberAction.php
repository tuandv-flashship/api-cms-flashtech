<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\RevokeRefreshTokensByAccessTokenIdTask;
use App\Containers\AppSection\Member\Values\MemberRefreshToken;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Cookie as CookieFacade;
use Symfony\Component\HttpFoundation\Cookie;

final class LogoutMemberAction extends ParentAction
{
    public function __construct(
        private readonly CreateMemberActivityLogTask $createMemberActivityLogTask,
        private readonly RevokeRefreshTokensByAccessTokenIdTask $revokeRefreshTokensByAccessTokenIdTask,
    ) {
    }

    public function run(Member|null $member): Cookie
    {
        if ($member) {
            $this->createMemberActivityLogTask->run([
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

        $this->revokeRefreshTokensByAccessTokenIdTask->run($tokenId);

        return CookieFacade::forget(MemberRefreshToken::cookieName());
    }
}
