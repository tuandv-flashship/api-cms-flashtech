<?php

namespace App\Containers\AppSection\Blog\Events;

use Illuminate\Queue\SerializesModels;

final class TagDeleted
{
    use SerializesModels;

    public function __construct(
        public readonly int $tagId,
        public readonly string $tagName
    ) {
    }
}
