<?php

namespace App\Containers\AppSection\Member\Data\Repositories;

use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Member
 *
 * @extends ParentRepository<TModel>
 */
final class MemberRepository extends ParentRepository
{
    protected int $maxPaginationLimit = 200;

    protected $fieldSearchable = [
        'id' => '=',
        'name' => 'like',
        'username' => 'like',
        'email' => '=',
        'status' => '=',
    ];

    public function model(): string
    {
        return Member::class;
    }
}
