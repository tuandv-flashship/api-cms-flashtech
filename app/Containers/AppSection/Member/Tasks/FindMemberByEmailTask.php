<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberRepository;
use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindMemberByEmailTask extends ParentTask
{
    public function __construct(
        private readonly MemberRepository $repository,
    ) {
    }

    public function run(string $email): Member|null
    {
        return $this->repository->findWhere(['email' => strtolower($email)])->first();
    }
}
