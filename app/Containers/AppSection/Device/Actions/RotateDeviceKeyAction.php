<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Exceptions\DeviceOperationException;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Tasks\FindDeviceByOwnerTask;
use App\Containers\AppSection\Device\Tasks\FindDeviceKeyByKeyIdTask;
use App\Containers\AppSection\Device\Tasks\ListDeviceKeyIdsByDeviceIdTask;
use App\Containers\AppSection\Device\Tasks\RevokeDeviceKeysByDeviceIdTask;
use App\Containers\AppSection\Device\Tasks\UpdateDeviceTask;
use App\Containers\AppSection\Device\Tasks\UpdateOrCreateDeviceKeyTask;
use App\Containers\AppSection\Device\Supports\DeviceSignatureCacheKey;
use App\Containers\AppSection\Device\Supports\PublicKeyValidator;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class RotateDeviceKeyAction extends ParentAction
{
    public function __construct(
        private readonly FindDeviceByOwnerTask $findDeviceByOwnerTask,
        private readonly FindDeviceKeyByKeyIdTask $findDeviceKeyByKeyIdTask,
        private readonly UpdateOrCreateDeviceKeyTask $updateOrCreateDeviceKeyTask,
        private readonly UpdateDeviceTask $updateDeviceTask,
        private readonly RevokeDeviceKeysByDeviceIdTask $revokeDeviceKeysByDeviceIdTask,
        private readonly ListDeviceKeyIdsByDeviceIdTask $listDeviceKeyIdsByDeviceIdTask,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId, string $keyId, string $publicKey): DeviceKey
    {
        return DB::transaction(function () use ($ownerType, $ownerId, $deviceId, $keyId, $publicKey): DeviceKey {
            $device = $this->findDeviceByOwnerTask->run($ownerType, $ownerId, $deviceId);
            $staleKeyIds = $this->listDeviceKeyIdsByDeviceIdTask->run($device->id);

            PublicKeyValidator::assertValidEd25519PublicKey($publicKey);

            $existingKey = $this->findDeviceKeyByKeyIdTask->run($keyId);

            if ($existingKey && $existingKey->device_id !== $device->id) {
                throw DeviceOperationException::keyIdTaken();
            }

            $this->revokeDeviceKeysByDeviceIdTask->run($device->id);

            $key = $this->updateOrCreateDeviceKeyTask->run(
                ['key_id' => $keyId],
                [
                    'device_id' => $device->id,
                    'public_key' => $publicKey,
                    'status' => DeviceKey::STATUS_ACTIVE,
                    'last_used_at' => now(),
                ],
            );

            $this->updateDeviceTask->run($device->id, ['last_seen_at' => now()]);
            $staleKeyIds[] = $keyId;
            foreach (array_unique($staleKeyIds) as $staleKeyId) {
                Cache::forget(DeviceSignatureCacheKey::keyContext($staleKeyId));
            }

            return $key;
        });
    }
}
