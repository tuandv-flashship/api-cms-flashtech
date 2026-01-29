<?php

namespace App\Containers\AppSection\Tools\UI\API\Transformers;

use App\Containers\AppSection\Tools\Supports\Import\ImportResult;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class DataSynchronizeImportTransformer extends ParentTransformer
{
    public function transform(ImportResult $result): array
    {
        return [
            'offset' => $result->offset,
            'count' => $result->count,
            'imported' => $result->imported,
            'failures' => $result->failures,
        ];
    }
}
