<?php

namespace App\Containers\AppSection\Member\Data\Repositories;

use App\Containers\AppSection\Member\Models\MemberActivityLog;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of MemberActivityLog
 *
 * @extends ParentRepository<TModel>
 */
final class MemberActivityLogRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'member_id' => '=',
        'action' => '=',
        'created_at' => '>=',
    ];

    public function model(): string
    {
        return MemberActivityLog::class;
    }
}
