<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Supports\DeviceSignatureCacheKey;
use App\Containers\AppSection\Device\Tasks\FindDeviceByOwnerTask;
use App\Containers\AppSection\Device\Tasks\ListDeviceKeyIdsByDeviceIdTask;
use App\Containers\AppSection\Device\Tasks\RevokeDeviceKeysByDeviceIdTask;
use App\Containers\AppSection\Device\Tasks\UpdateDeviceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class RevokeDeviceAction extends ParentAction
{
    public function __construct(
        private readonly FindDeviceByOwnerTask $findDeviceByOwnerTask,
        private readonly UpdateDeviceTask $updateDeviceTask,
        private readonly RevokeDeviceKeysByDeviceIdTask $revokeDeviceKeysByDeviceIdTask,
        private readonly ListDeviceKeyIdsByDeviceIdTask $listDeviceKeyIdsByDeviceIdTask,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId): Device
    {
        return DB::transaction(function () use ($ownerType, $ownerId, $deviceId): Device {
            $device = $this->findDeviceByOwnerTask->run($ownerType, $ownerId, $deviceId);
            $keyIds = $this->listDeviceKeyIdsByDeviceIdTask->run($device->id);

            $device = $this->updateDeviceTask->run($device->id, [
                'status' => DeviceStatus::REVOKED,
                'push_token' => null,
                'push_token_hash' => null,
                'push_provider' => null,
            ]);

            $this->revokeDeviceKeysByDeviceIdTask->run($device->id);
            foreach ($keyIds as $keyId) {
                Cache::forget(DeviceSignatureCacheKey::keyContext($keyId));
            }

            return $device->refresh();
        });
    }
}
