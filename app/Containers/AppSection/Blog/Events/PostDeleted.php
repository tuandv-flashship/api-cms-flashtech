<?php

namespace App\Containers\AppSection\Blog\Events;

use App\Containers\AppSection\Blog\Models\Post;
use Illuminate\Queue\SerializesModels;

final class PostDeleted
{
    use SerializesModels;

    public function __construct(
        public readonly int $postId,
        public readonly string $postName
    ) {
    }
}
