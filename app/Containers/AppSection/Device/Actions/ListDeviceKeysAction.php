<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Tasks\FindDeviceByOwnerTask;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Tasks\ListDeviceKeysTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListDeviceKeysAction extends ParentAction
{
    public function __construct(
        private readonly FindDeviceByOwnerTask $findDeviceByOwnerTask,
        private readonly ListDeviceKeysTask $listDeviceKeysTask,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId): LengthAwarePaginator
    {
        $device = $this->findDeviceByOwnerTask->run($ownerType, $ownerId, $deviceId);

        return $this->listDeviceKeysTask->run($device->id);
    }
}
