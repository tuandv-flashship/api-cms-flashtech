<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class RevokeDeviceKeysByDeviceIdTask extends ParentTask
{
    public function __construct(
        private readonly DeviceKeyRepository $repository,
    ) {
    }

    public function run(int $deviceId): int
    {
        return $this->repository->getModel()->newQuery()
            ->where('device_id', $deviceId)
            ->update(['status' => DeviceKey::STATUS_REVOKED]);
    }
}
