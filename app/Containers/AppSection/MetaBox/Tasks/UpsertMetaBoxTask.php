<?php

namespace App\Containers\AppSection\MetaBox\Tasks;

use App\Containers\AppSection\MetaBox\Models\MetaBox;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpsertMetaBoxTask extends ParentTask
{
    public function run(string $referenceType, int $referenceId, string $metaKey, mixed $value): MetaBox
    {
        return MetaBox::query()->updateOrCreate(
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'meta_key' => $metaKey,
            ],
            [
                'meta_value' => $value,
            ]
        );
    }
}
