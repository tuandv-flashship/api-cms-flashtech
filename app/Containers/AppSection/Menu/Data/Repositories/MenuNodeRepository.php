<?php

namespace App\Containers\AppSection\Menu\Data\Repositories;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of MenuNode
 *
 * @extends ParentRepository<TModel>
 */
final class MenuNodeRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'menu_id' => '=',
        'parent_id' => '=',
        'reference_type' => '=',
        'reference_id' => '=',
        'title' => 'like',
        'url' => 'like',
    ];

    public function model(): string
    {
        return MenuNode::class;
    }
}
