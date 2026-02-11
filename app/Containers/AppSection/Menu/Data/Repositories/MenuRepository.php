<?php

namespace App\Containers\AppSection\Menu\Data\Repositories;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Menu
 *
 * @extends ParentRepository<TModel>
 */
final class MenuRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'name' => 'like',
        'slug' => 'like',
        'status' => '=',
    ];

    public function model(): string
    {
        return Menu::class;
    }
}
