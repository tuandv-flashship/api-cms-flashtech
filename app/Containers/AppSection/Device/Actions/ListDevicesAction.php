<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Tasks\ListDevicesTask;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListDevicesAction extends ParentAction
{
    public function __construct(
        private readonly ListDevicesTask $listDevicesTask,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId): LengthAwarePaginator
    {
        return $this->listDevicesTask->run($ownerType, $ownerId);
    }
}
