<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ListDeviceKeyIdsByDeviceIdTask extends ParentTask
{
    public function __construct(
        private readonly DeviceKeyRepository $repository,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function run(int $deviceId): array
    {
        return $this->repository->getModel()->newQuery()
            ->where('device_id', $deviceId)
            ->pluck('key_id')
            ->filter(static fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values()
            ->all();
    }
}
