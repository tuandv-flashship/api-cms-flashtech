<?php

namespace App\Containers\AppSection\Blog\Data\Repositories;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Tag
 *
 * @extends ParentRepository<TModel>
 */
final class TagRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'name' => 'like',
        'status' => '=',
        'created_at' => 'between',
    ];

    public function model(): string
    {
        return Tag::class;
    }
}
