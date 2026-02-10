<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

final class RevokeRefreshTokensByAccessTokenIdTask extends ParentTask
{
    public function run(string $accessTokenId): void
    {
        DB::connection(config('passport.connection'))
            ->table('oauth_refresh_tokens')
            ->where('access_token_id', $accessTokenId)
            ->update(['revoked' => true]);
    }
}
