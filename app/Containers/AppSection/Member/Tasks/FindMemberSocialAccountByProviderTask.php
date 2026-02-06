<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberSocialAccountRepository;
use App\Containers\AppSection\Member\Models\MemberSocialAccount;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindMemberSocialAccountByProviderTask extends ParentTask
{
    public function __construct(
        private readonly MemberSocialAccountRepository $repository,
    ) {
    }

    public function run(string $provider, string $providerId): MemberSocialAccount|null
    {
        return $this->repository->findWhere([
            'provider' => $provider,
            'provider_id' => $providerId,
        ])->first();
    }
}
