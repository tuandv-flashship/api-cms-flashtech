<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Data\Repositories\MemberActivityLogRepository;
use App\Containers\AppSection\Member\Models\MemberActivityLog;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateMemberActivityLogTask extends ParentTask
{
    public function __construct(
        private readonly MemberActivityLogRepository $repository
    ) {
    }

    public function run(array $data): MemberActivityLog
    {
        return $this->repository->create($data);
    }
}
