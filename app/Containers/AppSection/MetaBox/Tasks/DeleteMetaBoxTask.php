<?php

namespace App\Containers\AppSection\MetaBox\Tasks;

use App\Containers\AppSection\MetaBox\Models\MetaBox;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteMetaBoxTask extends ParentTask
{
    public function run(string $referenceType, int $referenceId, string $metaKey): void
    {
        MetaBox::query()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('meta_key', $metaKey)
            ->delete();
    }
}
