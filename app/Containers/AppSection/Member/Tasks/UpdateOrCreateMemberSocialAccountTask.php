<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberSocialAccountRepository;
use App\Containers\AppSection\Member\Models\MemberSocialAccount;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateOrCreateMemberSocialAccountTask extends ParentTask
{
    public function __construct(
        private readonly MemberSocialAccountRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     */
    public function run(array $attributes, array $values): MemberSocialAccount
    {
        return $this->repository->getModel()->newQuery()->updateOrCreate($attributes, $values);
    }
}
