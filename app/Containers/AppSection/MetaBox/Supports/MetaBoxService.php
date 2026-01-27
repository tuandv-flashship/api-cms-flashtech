<?php

namespace App\Containers\AppSection\MetaBox\Supports;

use App\Containers\AppSection\MetaBox\Tasks\DeleteMetaBoxTask;
use App\Containers\AppSection\MetaBox\Tasks\GetMetaBoxValueTask;
use App\Containers\AppSection\MetaBox\Tasks\UpsertMetaBoxTask;
use Illuminate\Database\Eloquent\Model;

final class MetaBoxService
{
    public function __construct(
        private readonly GetMetaBoxValueTask $getMetaBoxValueTask,
        private readonly UpsertMetaBoxTask $upsertMetaBoxTask,
        private readonly DeleteMetaBoxTask $deleteMetaBoxTask
    ) {
    }

    public function getMetaData(Model $reference, string $metaKey, mixed $default = null): mixed
    {
        return $this->getMetaByReference(
            $this->getReferenceType($reference),
            (int) $reference->getKey(),
            $metaKey,
            $default
        );
    }

    public function saveMetaData(Model $reference, string $metaKey, mixed $value): void
    {
        $this->upsertMetaBoxTask->run(
            $this->getReferenceType($reference),
            (int) $reference->getKey(),
            $metaKey,
            $value
        );
    }

    public function deleteMetaData(Model $reference, string $metaKey): void
    {
        $this->deleteMetaBoxTask->run(
            $this->getReferenceType($reference),
            (int) $reference->getKey(),
            $metaKey
        );
    }

    public function getMetaByReference(
        string $referenceType,
        int $referenceId,
        string $metaKey,
        mixed $default = null
    ): mixed {
        return $this->getMetaBoxValueTask->run($referenceType, $referenceId, $metaKey, $default);
    }

    private function getReferenceType(Model $reference): string
    {
        return method_exists($reference, 'getMorphClass')
            ? $reference->getMorphClass()
            : $reference::class;
    }
}
