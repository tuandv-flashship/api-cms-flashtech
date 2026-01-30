<?php

namespace App\Containers\AppSection\Blog\Events;

use Illuminate\Queue\SerializesModels;

final class CategoryDeleted
{
    use SerializesModels;

    public function __construct(
        public readonly int $categoryId,
        public readonly string $categoryName
    ) {
    }
}
