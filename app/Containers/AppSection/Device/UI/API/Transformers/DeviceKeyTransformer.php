<?php

namespace App\Containers\AppSection\Device\UI\API\Transformers;

use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class DeviceKeyTransformer extends ParentTransformer
{
    public function __construct(
        private readonly bool $includePublicKey = false,
    ) {
    }

    public function transform(DeviceKey $key): array
    {
        return [
            'object' => 'DeviceKey',
            'id' => $key->key_id,
            'key_id' => $key->key_id,
            'status' => $key->status,
            'last_used_at' => $key->last_used_at,
            'created_at' => $key->created_at,
            ...($this->includePublicKey ? ['public_key' => $key->public_key] : []),
        ];
    }
}
