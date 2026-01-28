<?php

namespace App\Containers\AppSection\Blog\Data\Repositories;

use App\Containers\AppSection\Blog\Models\Category;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Category
 *
 * @extends ParentRepository<TModel>
 */
final class CategoryRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'name' => 'like',
        'status' => '=',
        'parent_id' => '=',
        'is_featured' => '=',
        'is_default' => '=',
    ];

    public function model(): string
    {
        return Category::class;
    }
}
