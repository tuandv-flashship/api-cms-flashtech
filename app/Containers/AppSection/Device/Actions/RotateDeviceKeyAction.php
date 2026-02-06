<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Containers\AppSection\Device\Tasks\FindDeviceByOwnerTask;
use App\Containers\AppSection\Device\Tasks\FindDeviceKeyByKeyIdTask;
use App\Containers\AppSection\Device\Tasks\UpdateDeviceTask;
use App\Containers\AppSection\Device\Tasks\UpdateOrCreateDeviceKeyTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RotateDeviceKeyAction extends ParentAction
{
    public function __construct(
        private readonly FindDeviceByOwnerTask $findDeviceByOwnerTask,
        private readonly FindDeviceKeyByKeyIdTask $findDeviceKeyByKeyIdTask,
        private readonly UpdateOrCreateDeviceKeyTask $updateOrCreateDeviceKeyTask,
        private readonly UpdateDeviceTask $updateDeviceTask,
        private readonly DeviceKeyRepository $deviceKeyRepository,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId, string $keyId, string $publicKey): DeviceKey
    {
        return DB::transaction(function () use ($ownerType, $ownerId, $deviceId, $keyId, $publicKey): DeviceKey {
            $device = $this->findDeviceByOwnerTask->run($ownerType, $ownerId, $deviceId);

            $this->assertValidPublicKey($publicKey);

            $existingKey = $this->findDeviceKeyByKeyIdTask->run($keyId);

            if ($existingKey && $existingKey->device_id !== $device->id) {
                throw ValidationException::withMessages([
                    'key_id' => ['The key_id has already been taken.'],
                ]);
            }

            $this->deviceKeyRepository->getModel()->newQuery()
                ->where('device_id', $device->id)
                ->update(['status' => DeviceKey::STATUS_REVOKED]);

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

            return $key;
        });
    }

    private function assertValidPublicKey(string $publicKey): void
    {
        $decoded = $this->base64UrlDecode($publicKey);

        if ($decoded === null) {
            throw ValidationException::withMessages([
                'public_key' => ['The public_key is invalid.'],
            ]);
        }

        if (defined('SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES')) {
            $expected = SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES;
            if (strlen($decoded) !== $expected) {
                throw ValidationException::withMessages([
                    'public_key' => ['The public_key length is invalid.'],
                ]);
            }
        }
    }

    private function base64UrlDecode(string $value): string|null
    {
        $padded = strtr($value, '-_', '+/');
        $padLength = strlen($padded) % 4;
        if ($padLength > 0) {
            $padded .= str_repeat('=', 4 - $padLength);
        }

        $decoded = base64_decode($padded, true);

        return $decoded === false ? null : $decoded;
    }
}
