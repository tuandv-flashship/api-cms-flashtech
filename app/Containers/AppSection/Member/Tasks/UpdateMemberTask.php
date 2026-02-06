<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberRepository;
use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Exceptions\UpdateResourceFailedException;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Exception;

final class UpdateMemberTask extends ParentTask
{
    public function __construct(
        private readonly MemberRepository $repository
    ) {
    }

    public function run(int $memberId, array $data): Member
    {
        try {
            return $this->repository->update($data, $memberId);
        } catch (Exception) {
            throw new UpdateResourceFailedException();
        }
    }
}
