<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindDeviceKeyByKeyIdTask extends ParentTask
{
    public function __construct(
        private readonly DeviceKeyRepository $repository,
    ) {
    }

    public function run(string $keyId): DeviceKey|null
    {
        return $this->repository->findWhere(['key_id' => $keyId])->first();
    }
}
