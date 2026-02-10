<?php

namespace App\Containers\AppSection\Revision\Data\Repositories;

use App\Containers\AppSection\Revision\Models\Revision;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Revision
 *
 * @extends ParentRepository<TModel>
 */
final class RevisionRepository extends ParentRepository
{
    protected int $maxPaginationLimit = 200;

    protected $fieldSearchable = [
        'id' => '=',
        'key' => 'like',
    ];

    public function model(): string
    {
        return Revision::class;
    }
}
