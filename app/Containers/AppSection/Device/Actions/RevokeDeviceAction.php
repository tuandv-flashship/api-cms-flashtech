<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Tasks\FindDeviceByOwnerTask;
use App\Containers\AppSection\Device\Tasks\UpdateDeviceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class RevokeDeviceAction extends ParentAction
{
    public function __construct(
        private readonly FindDeviceByOwnerTask $findDeviceByOwnerTask,
        private readonly UpdateDeviceTask $updateDeviceTask,
        private readonly DeviceKeyRepository $deviceKeyRepository,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId): Device
    {
        return DB::transaction(function () use ($ownerType, $ownerId, $deviceId): Device {
            $device = $this->findDeviceByOwnerTask->run($ownerType, $ownerId, $deviceId);

            $device = $this->updateDeviceTask->run($device->id, [
                'status' => DeviceStatus::REVOKED,
                'push_token' => null,
                'push_token_hash' => null,
                'push_provider' => null,
            ]);

            $this->deviceKeyRepository->getModel()->newQuery()
                ->where('device_id', $device->id)
                ->update(['status' => DeviceKey::STATUS_REVOKED]);

            return $device->refresh();
        });
    }
}
