<?php

namespace App\Containers\AppSection\Member\Data\Repositories;

use App\Ship\Parents\Repositories\Repository as ParentRepository;

class MemberRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'name' => 'like',
        'email' => '=',
    ];
}
