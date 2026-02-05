<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Apiato\Core\Page\Page;

class GetAllMembersTask extends ParentTask
{
    public function __construct(
        protected MemberRepository $repository
    ) {
    }

    public function run(): mixed
    {
        return $this->repository->paginate();
    }
}
