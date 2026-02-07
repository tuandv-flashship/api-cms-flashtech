<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceRepository;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Exceptions\DeviceOperationException;
use App\Containers\AppSection\Device\Models\Device;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindDeviceByOwnerTask extends ParentTask
{
    public function __construct(
        private readonly DeviceRepository $repository,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId): Device
    {
        $ownerTypeValue = $ownerType->value;

        $device = $this->repository->findWhere([
            'owner_type' => $ownerTypeValue,
            'owner_id' => $ownerId,
            'device_id' => $deviceId,
        ])->first();

        if (! $device) {
            throw DeviceOperationException::deviceNotFound();
        }

        return $device;
    }
}
