<?php

namespace App\Containers\AppSection\Device\Enums;

enum DeviceStatus: string
{
    case ACTIVE = 'active';
    case REVOKED = 'revoked';
}
