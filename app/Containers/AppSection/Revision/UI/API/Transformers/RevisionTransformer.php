<?php

namespace App\Containers\AppSection\Revision\UI\API\Transformers;

use App\Containers\AppSection\Revision\Models\Revision;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class RevisionTransformer extends ParentTransformer
{
    public function transform(Revision $revision): array
    {
        return [
            'id' => $revision->getHashedKey(),
            'revisionable_type' => $revision->revisionable_type,
            'revisionable_id' => $this->hashId($revision->revisionable_id),
            'key' => $revision->key,
            'field' => $revision->fieldName(),
            'old_value' => $revision->oldValue(),
            'new_value' => $revision->newValue(),
            'user_id' => $this->hashId($revision->user_id),
            'user_name' => $revision->userResponsible()?->name,
            'created_at' => $revision->created_at?->toISOString(),
            'updated_at' => $revision->updated_at?->toISOString(),
        ];
    }

    private function hashId(int|string|null $id): int|string|null
    {
        if ($id === null) {
            return null;
        }

        $intId = (int) $id;
        if ($intId <= 0) {
            return $intId;
        }

        return config('apiato.hash-id') ? hashids()->encodeOrFail($intId) : $intId;
    }
}
