<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberRepository;
use App\Ship\Exceptions\DeleteResourceFailedException;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Exception;

class DeleteMemberTask extends ParentTask
{
    public function __construct(
        protected MemberRepository $repository
    ) {
    }

    public function run($id): int
    {
        try {
            return $this->repository->delete($id);
        } catch (Exception) {
            throw new DeleteResourceFailedException();
        }
    }
}
