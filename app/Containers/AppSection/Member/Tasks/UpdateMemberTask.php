<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Exception;

class UpdateMemberTask extends ParentTask
{
    public function run(Member $member, array $data): Member
    {
        $member->update($data);
        return $member->refresh();
    }
}
