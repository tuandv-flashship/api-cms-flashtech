<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Values\ActiveDeviceKeyContext;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindActiveDeviceKeyContextTask extends ParentTask
{
    public function __construct(
        private readonly DeviceKeyRepository $repository,
    ) {
    }

    public function run(string $keyId): ActiveDeviceKeyContext|null
    {
        if ($keyId === '') {
            return null;
        }

        $record = $this->repository
            ->getModel()
            ->newQuery()
            ->join('devices', 'devices.id', '=', 'device_keys.device_id')
            ->where('device_keys.key_id', $keyId)
            ->where('device_keys.status', DeviceKey::STATUS_ACTIVE)
            ->where('devices.status', DeviceStatus::ACTIVE->value)
            ->select([
                'device_keys.id as device_key_id',
                'device_keys.device_id',
                'device_keys.public_key',
                'devices.owner_type',
                'devices.owner_id',
            ])
            ->first();

        if (!$record) {
            return null;
        }

        return ActiveDeviceKeyContext::createFromRecord($record);
    }
}
