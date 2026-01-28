<?php

namespace App\Containers\AppSection\Blog\Enums;

enum ContentStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case PENDING = 'pending';
}
