<?php

namespace App\Containers\AppSection\Tools\UI\API\Transformers;

use App\Containers\AppSection\Tools\Supports\Import\ValidationResult;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class DataSynchronizeValidationTransformer extends ParentTransformer
{
    public function transform(ValidationResult $result): array
    {
        return [
            'file_name' => $result->fileName,
            'offset' => $result->offset,
            'count' => $result->count,
            'total' => $result->total,
            'errors' => $result->errors,
        ];
    }
}
