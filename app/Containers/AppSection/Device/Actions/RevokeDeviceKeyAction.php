<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Exceptions\DeviceOperationException;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Supports\DeviceSignatureCacheKey;
use App\Containers\AppSection\Device\Tasks\FindDeviceByOwnerTask;
use App\Containers\AppSection\Device\Tasks\FindDeviceKeyByDeviceAndKeyIdTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class RevokeDeviceKeyAction extends ParentAction
{
    public function __construct(
        private readonly FindDeviceByOwnerTask $findDeviceByOwnerTask,
        private readonly FindDeviceKeyByDeviceAndKeyIdTask $findDeviceKeyByDeviceAndKeyIdTask,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId, string $keyId): DeviceKey
    {
        return DB::transaction(function () use ($ownerType, $ownerId, $deviceId, $keyId): DeviceKey {
            $device = $this->findDeviceByOwnerTask->run($ownerType, $ownerId, $deviceId);

            $deviceKey = $this->findDeviceKeyByDeviceAndKeyIdTask->run($device->id, $keyId);

            if (! $deviceKey) {
                throw DeviceOperationException::deviceKeyNotFound();
            }

            $deviceKey->status = DeviceKey::STATUS_REVOKED;
            $deviceKey->save();
            Cache::forget(DeviceSignatureCacheKey::keyContext($deviceKey->key_id));

            return $deviceKey->refresh();
        });
    }
}
