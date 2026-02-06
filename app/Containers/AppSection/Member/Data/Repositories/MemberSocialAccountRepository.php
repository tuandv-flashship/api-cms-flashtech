<?php

namespace App\Containers\AppSection\Member\Data\Repositories;

use App\Containers\AppSection\Member\Models\MemberSocialAccount;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of MemberSocialAccount
 *
 * @extends ParentRepository<TModel>
 */
final class MemberSocialAccountRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'member_id' => '=',
        'provider' => '=',
        'provider_id' => '=',
    ];

    public function model(): string
    {
        return MemberSocialAccount::class;
    }
}
