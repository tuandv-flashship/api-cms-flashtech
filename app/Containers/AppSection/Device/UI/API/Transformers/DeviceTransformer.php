<?php

namespace App\Containers\AppSection\Device\UI\API\Transformers;

use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceKeyTransformer;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Collection;

final class DeviceTransformer extends ParentTransformer
{
    protected array $availableIncludes = [
        'keys',
    ];

    public function transform(Device $device): array
    {
        $status = $device->status;

        return [
            'object' => 'Device',
            'id' => $device->device_id,
            'device_id' => $device->device_id,
            'platform' => $device->platform,
            'device_name' => $device->device_name,
            'push_provider' => $device->push_provider,
            'app_version' => $device->app_version,
            'status' => $status instanceof DeviceStatus ? $status->value : $status,
            'last_seen_at' => $device->last_seen_at,
            'created_at' => $device->created_at,
            'updated_at' => $device->updated_at,
        ];
    }

    public function includeKeys(Device $device): Collection
    {
        $device->loadMissing('keys');

        return $this->collection($device->keys, new DeviceKeyTransformer());
    }
}
