<?php

namespace App\Containers\AppSection\Blog\Events;

use App\Containers\AppSection\Blog\Models\Tag;
use Illuminate\Queue\SerializesModels;

final class TagCreated
{
    use SerializesModels;

    public function __construct(
        public readonly Tag $tag
    ) {
    }
}
