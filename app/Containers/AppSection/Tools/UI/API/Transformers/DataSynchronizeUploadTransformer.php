<?php

namespace App\Containers\AppSection\Tools\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use Illuminate\Contracts\Support\Arrayable;

final class DataSynchronizeUploadTransformer extends ParentTransformer
{
    /**
     * @param array<string, mixed>|Arrayable|object|string $data
     */
    public function transform(mixed $data): array
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }

        if (! is_array($data)) {
            $data = ['file_name' => (string) $data];
        }

        return [
            'file_name' => $data['file_name'] ?? null,
            'original_name' => $data['original_name'] ?? null,
            'size' => $data['size'] ?? null,
            'mime_type' => $data['mime_type'] ?? null,
        ];
    }
}
