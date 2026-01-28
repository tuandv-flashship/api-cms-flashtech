<?php

namespace App\Containers\AppSection\Blog\Data\Repositories;

use App\Containers\AppSection\Blog\Models\Post;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Post
 *
 * @extends ParentRepository<TModel>
 */
final class PostRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'id' => '=',
        'name' => 'like',
        'status' => '=',
        'author_id' => '=',
        'is_featured' => '=',
    ];

    public function model(): string
    {
        return Post::class;
    }
}
