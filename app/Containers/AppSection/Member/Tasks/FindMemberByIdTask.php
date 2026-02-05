<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberRepository;
use App\Containers\AppSection\Member\Models\Member;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FindMemberByIdTask extends ParentTask
{
    public function __construct(
        protected MemberRepository $repository
    ) {
    }

    public function run($id): Member
    {
        try {
            return $this->repository->find($id);
        } catch (Exception) {
            throw new NotFoundHttpException();
        }
    }
}
