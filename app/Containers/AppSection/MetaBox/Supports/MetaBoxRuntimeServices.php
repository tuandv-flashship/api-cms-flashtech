<?php

namespace App\Containers\AppSection\MetaBox\Supports;

use App\Containers\AppSection\MetaBox\Tasks\DeleteMetaBoxTask;
use App\Containers\AppSection\MetaBox\Tasks\GetMetaBoxValueTask;
use App\Containers\AppSection\MetaBox\Tasks\UpsertMetaBoxTask;

final class MetaBoxRuntimeServices
{
    private static MetaBoxService|null $service = null;

    public static function service(): MetaBoxService
    {
        if (self::$service === null) {
            self::$service = new MetaBoxService(
                new GetMetaBoxValueTask(),
                new UpsertMetaBoxTask(),
                new DeleteMetaBoxTask(),
            );
        }

        return self::$service;
    }

    public static function reset(): void
    {
        self::$service = null;
    }
}
