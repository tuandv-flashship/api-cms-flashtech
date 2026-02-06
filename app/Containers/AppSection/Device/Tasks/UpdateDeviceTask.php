<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceRepository;
use App\Containers\AppSection\Device\Models\Device;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateDeviceTask extends ParentTask
{
    public function __construct(
        private readonly DeviceRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function run(int $id, array $properties): Device
    {
        return $this->repository->update($properties, $id);
    }
}
