<?php

namespace App\Containers\AppSection\Menu\Data\Repositories;

use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of MenuLocation
 *
 * @extends ParentRepository<TModel>
 */
final class MenuLocationRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'menu_id' => '=',
        'location' => '=',
    ];

    public function model(): string
    {
        return MenuLocation::class;
    }
}
