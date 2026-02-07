<?php

namespace App\Containers\AppSection\Device\Supports;

final class PushTokenHasher
{
    public static function hash(string|null $token): string|null
    {
        if ($token === null || $token === '') {
            return null;
        }

        return hash('sha256', $token);
    }
}

