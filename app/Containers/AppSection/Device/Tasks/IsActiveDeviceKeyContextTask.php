<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class IsActiveDeviceKeyContextTask extends ParentTask
{
    public function __construct(
        private readonly DeviceKeyRepository $repository,
    ) {
    }

    public function run(string $keyId, int $deviceKeyId, int $deviceId): bool
    {
        if ($keyId === '' || $deviceKeyId <= 0 || $deviceId <= 0) {
            return false;
        }

        return $this->repository
            ->getModel()
            ->newQuery()
            ->join('devices', 'devices.id', '=', 'device_keys.device_id')
            ->where('device_keys.id', $deviceKeyId)
            ->where('device_keys.device_id', $deviceId)
            ->where('device_keys.key_id', $keyId)
            ->where('device_keys.status', DeviceKey::STATUS_ACTIVE)
            ->where('devices.status', DeviceStatus::ACTIVE->value)
            ->exists();
    }
}
