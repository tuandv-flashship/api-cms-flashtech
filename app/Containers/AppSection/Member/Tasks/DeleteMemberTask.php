<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberRepository;
use App\Ship\Exceptions\DeleteResourceFailedException;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Exception;

final class DeleteMemberTask extends ParentTask
{
    public function __construct(
        private readonly MemberRepository $repository
    ) {
    }

    public function run(int|string $id): int
    {
        try {
            return $this->repository->delete($id);
        } catch (Exception) {
            throw new DeleteResourceFailedException();
        }
    }
}
