<?php

namespace App\Containers\AppSection\Menu\Data\Repositories;

use App\Containers\AppSection\Menu\Models\MenuNodeTranslation;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of MenuNodeTranslation
 *
 * @extends ParentRepository<TModel>
 */
final class MenuNodeTranslationRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'lang_code' => '=',
        'menu_nodes_id' => '=',
    ];

    public function model(): string
    {
        return MenuNodeTranslation::class;
    }
}
