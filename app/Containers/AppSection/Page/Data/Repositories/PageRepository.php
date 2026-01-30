<?php

namespace App\Containers\AppSection\Page\Data\Repositories;

use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Page
 *
 * @extends ParentRepository<TModel>
 */
final class PageRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'name' => 'like',
        'status' => '=',
        'user_id' => '=',
        'template' => '=',
    ];

    public function model(): string
    {
        return Page::class;
    }
}
