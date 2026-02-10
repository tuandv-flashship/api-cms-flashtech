<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Exceptions\DeviceOperationException;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Tasks\FindDeviceKeyByKeyIdTask;
use App\Containers\AppSection\Device\Tasks\NullifyDevicesByPushTokenHashTask;
use App\Containers\AppSection\Device\Tasks\UpdateOrCreateDeviceKeyTask;
use App\Containers\AppSection\Device\Tasks\UpdateOrCreateDeviceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Containers\AppSection\Device\Supports\DeviceSignatureCacheKey;
use App\Containers\AppSection\Device\Supports\PublicKeyValidator;
use App\Containers\AppSection\Device\Supports\PushTokenHasher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class RegisterDeviceAction extends ParentAction
{
    public function __construct(
        private readonly UpdateOrCreateDeviceTask $updateOrCreateDeviceTask,
        private readonly UpdateOrCreateDeviceKeyTask $updateOrCreateDeviceKeyTask,
        private readonly FindDeviceKeyByKeyIdTask $findDeviceKeyByKeyIdTask,
        private readonly NullifyDevicesByPushTokenHashTask $nullifyDevicesByPushTokenHashTask,
    ) {
    }

    /**
     * @return array{device: Device, key: DeviceKey}
     */
    public function run(array $payload, DeviceOwnerType $ownerType, int $ownerId): array
    {
        return DB::transaction(function () use ($payload, $ownerType, $ownerId): array {
            $deviceId = (string) $payload['device_id'];
            $keyId = (string) $payload['key_id'];
            $publicKey = (string) $payload['public_key'];

            PublicKeyValidator::assertValidEd25519PublicKey($publicKey);

            $ownerTypeValue = $ownerType->value;

            $deviceAttributes = [
                'owner_type' => $ownerTypeValue,
                'owner_id' => $ownerId,
                'device_id' => $deviceId,
            ];

            $deviceUpdates = [
                'status' => DeviceStatus::ACTIVE,
                'last_seen_at' => now(),
            ];

            $optionalFields = [
                'platform',
                'device_name',
                'push_token',
                'push_provider',
                'app_version',
            ];

            foreach ($optionalFields as $field) {
                if (array_key_exists($field, $payload)) {
                    $deviceUpdates[$field] = $payload[$field];
                }
            }

            if (array_key_exists('push_token', $payload)) {
                $deviceUpdates['push_token_hash'] = PushTokenHasher::hash($payload['push_token'] ?? null);
            }

            if (!empty($deviceUpdates['push_token_hash']) && !empty($deviceUpdates['push_provider'])) {
                $this->nullifyDevicesByPushTokenHashTask->run(
                    (string) $deviceUpdates['push_provider'],
                    (string) $deviceUpdates['push_token_hash'],
                );
            }

            $device = $this->updateOrCreateDeviceTask->run($deviceAttributes, $deviceUpdates);

            $existingKey = $this->findDeviceKeyByKeyIdTask->run($keyId);

            if ($existingKey && $existingKey->device_id !== $device->id) {
                throw DeviceOperationException::keyIdTaken();
            }

            $key = $this->updateOrCreateDeviceKeyTask->run(
                ['key_id' => $keyId],
                [
                    'device_id' => $device->id,
                    'public_key' => $publicKey,
                    'status' => DeviceKey::STATUS_ACTIVE,
                    'last_used_at' => now(),
                ],
            );

            Cache::forget(DeviceSignatureCacheKey::keyContext((string) $key->key_id));

            return [
                'device' => $device,
                'key' => $key,
            ];
        });
    }
}
