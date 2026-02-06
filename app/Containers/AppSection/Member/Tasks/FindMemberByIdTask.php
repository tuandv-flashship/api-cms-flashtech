<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberRepository;
use App\Containers\AppSection\Member\Models\Member;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindMemberByIdTask extends ParentTask
{
    public function __construct(
        private readonly MemberRepository $repository
    ) {
    }

    public function run(int|string $id): Member
    {
        try {
            return $this->repository->find($id);
        } catch (Exception) {
            throw new NotFoundHttpException();
        }
    }
}
