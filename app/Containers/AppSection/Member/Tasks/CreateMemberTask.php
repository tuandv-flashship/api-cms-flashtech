<?php

namespace App\Containers\AppSection\Member\Tasks;

use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Exceptions\CreateResourceFailedException;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Exception;

class CreateMemberTask extends ParentTask
{
    public function run(array $data): Member
    {
        try {
            return Member::create($data);
        } catch (Exception) {
            throw new CreateResourceFailedException();
        }
    }
}
