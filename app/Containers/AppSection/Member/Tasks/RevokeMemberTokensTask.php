<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

final class RevokeMemberTokensTask extends ParentTask
{
    public function run(Member $member): void
    {
        $clientIds = $this->resolveClientIds();

        if ($clientIds === []) {
            return;
        }

        $connection = config('passport.connection');

        $tokenIds = DB::connection($connection)
            ->table('oauth_access_tokens')
            ->where('user_id', $member->getKey())
            ->whereIn('client_id', $clientIds)
            ->pluck('id')
            ->all();

        if ($tokenIds === []) {
            return;
        }

        DB::connection($connection)
            ->table('oauth_access_tokens')
            ->whereIn('id', $tokenIds)
            ->update(['revoked' => true]);

        DB::connection($connection)
            ->table('oauth_refresh_tokens')
            ->whereIn('access_token_id', $tokenIds)
            ->update(['revoked' => true]);
    }

    /**
     * @return array<int, string>
     */
    private function resolveClientIds(): array
    {
        $ids = array_filter([
            config('appSection-authentication.clients.member.id'),
            config('appSection-authentication.clients.mobile.id'),
        ], static fn ($value) => !is_null($value) && $value !== '');

        if ($ids !== []) {
            return array_values($ids);
        }

        return DB::connection(config('passport.connection'))
            ->table('oauth_clients')
            ->where('provider', 'members')
            ->pluck('id')
            ->all();
    }
}
