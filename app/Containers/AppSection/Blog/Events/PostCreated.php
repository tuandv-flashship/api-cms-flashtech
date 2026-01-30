<?php

namespace App\Containers\AppSection\Blog\Events;

use App\Containers\AppSection\Blog\Models\Post;
use Illuminate\Queue\SerializesModels;

final class PostCreated
{
    use SerializesModels;

    public function __construct(
        public readonly Post $post
    ) {
    }
}
