<?php

namespace App\Containers\AppSection\MetaBox\Traits;

use App\Containers\AppSection\MetaBox\Supports\MetaBoxRuntimeServices;

trait HasMetaBoxes
{
    public function getMeta(string $metaKey, mixed $default = null): mixed
    {
        return MetaBoxRuntimeServices::service()->getMetaData($this, $metaKey, $default);
    }

    public function setMeta(string $metaKey, mixed $value): void
    {
        MetaBoxRuntimeServices::service()->saveMetaData($this, $metaKey, $value);
    }

    public function deleteMeta(string $metaKey): void
    {
        MetaBoxRuntimeServices::service()->deleteMetaData($this, $metaKey);
    }
}
