<?php

namespace App\Containers\AppSection\Member\Enums;

enum MemberStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
