<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceRepository;
use App\Containers\AppSection\Device\Models\Device;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateOrCreateDeviceTask extends ParentTask
{
    public function __construct(
        private readonly DeviceRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     */
    public function run(array $attributes, array $values): Device
    {
        return $this->repository->getModel()->newQuery()->updateOrCreate($attributes, $values);
    }
}
