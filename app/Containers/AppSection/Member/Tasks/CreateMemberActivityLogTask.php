<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Models\MemberActivityLog;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateMemberActivityLogTask extends ParentTask
{
    public function run(array $data): MemberActivityLog
    {
        return MemberActivityLog::create($data);
    }
}
