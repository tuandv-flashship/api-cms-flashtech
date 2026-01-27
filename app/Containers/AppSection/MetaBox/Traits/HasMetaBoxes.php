<?php

namespace App\Containers\AppSection\MetaBox\Traits;

use App\Containers\AppSection\MetaBox\Supports\MetaBoxService;

trait HasMetaBoxes
{
    public function getMeta(string $metaKey, mixed $default = null): mixed
    {
        return app(MetaBoxService::class)->getMetaData($this, $metaKey, $default);
    }

    public function setMeta(string $metaKey, mixed $value): void
    {
        app(MetaBoxService::class)->saveMetaData($this, $metaKey, $value);
    }

    public function deleteMeta(string $metaKey): void
    {
        app(MetaBoxService::class)->deleteMetaData($this, $metaKey);
    }
}
