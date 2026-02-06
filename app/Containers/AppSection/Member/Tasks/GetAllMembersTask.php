<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetAllMembersTask extends ParentTask
{
    public function __construct(
        private readonly MemberRepository $repository
    ) {
    }

    public function run(): LengthAwarePaginator
    {
        return $this->repository
            ->addRequestCriteria()
            ->paginate();
    }
}
