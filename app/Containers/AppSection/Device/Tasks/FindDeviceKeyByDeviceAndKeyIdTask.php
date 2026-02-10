<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindDeviceKeyByDeviceAndKeyIdTask extends ParentTask
{
    public function __construct(
        private readonly DeviceKeyRepository $repository,
    ) {
    }

    public function run(int $deviceId, string $keyId): DeviceKey|null
    {
        return $this->repository->findWhere([
            'device_id' => $deviceId,
            'key_id' => $keyId,
        ])->first();
    }
}

