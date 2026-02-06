<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Tasks\FindDeviceByOwnerTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class RevokeDeviceKeyAction extends ParentAction
{
    public function __construct(
        private readonly FindDeviceByOwnerTask $findDeviceByOwnerTask,
        private readonly DeviceKeyRepository $deviceKeyRepository,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId, string $keyId): DeviceKey
    {
        return DB::transaction(function () use ($ownerType, $ownerId, $deviceId, $keyId): DeviceKey {
            $device = $this->findDeviceByOwnerTask->run($ownerType, $ownerId, $deviceId);

            $deviceKey = $this->deviceKeyRepository->getModel()->newQuery()
                ->where('device_id', $device->id)
                ->where('key_id', $keyId)
                ->first();

            if (! $deviceKey) {
                throw (new ModelNotFoundException())->setModel(DeviceKey::class);
            }

            $deviceKey->status = DeviceKey::STATUS_REVOKED;
            $deviceKey->save();

            return $deviceKey->refresh();
        });
    }
}
