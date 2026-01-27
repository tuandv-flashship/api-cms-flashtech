<?php

namespace App\Containers\AppSection\Media\UI\API\Transformers;

use App\Containers\AppSection\Media\Models\MediaFolder;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class MediaFolderTransformer extends ParentTransformer
{
    public function transform(MediaFolder $folder): array
    {
        return [
            'id' => $folder->getHashedKey(),
            'name' => $folder->name,
            'color' => $folder->color,
            'created_at' => $folder->created_at?->toISOString(),
            'updated_at' => $folder->updated_at?->toISOString(),
        ];
    }
}
