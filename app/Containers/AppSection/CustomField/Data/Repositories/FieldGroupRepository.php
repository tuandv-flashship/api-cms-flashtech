<?php

namespace App\Containers\AppSection\CustomField\Data\Repositories;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of FieldGroup
 *
 * @extends ParentRepository<TModel>
 */
final class FieldGroupRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'title' => 'like',
        'status' => '=',
    ];

    public function model(): string
    {
        return FieldGroup::class;
    }
}
