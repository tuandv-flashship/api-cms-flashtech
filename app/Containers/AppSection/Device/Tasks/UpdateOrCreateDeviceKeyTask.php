<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateOrCreateDeviceKeyTask extends ParentTask
{
    public function __construct(
        private readonly DeviceKeyRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     */
    public function run(array $attributes, array $values): DeviceKey
    {
        return $this->repository->getModel()->newQuery()->updateOrCreate($attributes, $values);
    }
}
