<?php

namespace App\Containers\AppSection\Gallery\Data\Repositories;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Gallery
 *
 * @extends ParentRepository<TModel>
 */
final class GalleryRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'name' => 'like',
        'status' => '=',
    ];

    public function model(): string
    {
        return Gallery::class;
    }
}
