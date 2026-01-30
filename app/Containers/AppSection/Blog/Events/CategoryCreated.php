<?php

namespace App\Containers\AppSection\Blog\Events;

use App\Containers\AppSection\Blog\Models\Category;
use Illuminate\Queue\SerializesModels;

final class CategoryCreated
{
    use SerializesModels;

    public function __construct(
        public readonly Category $category
    ) {
    }
}
