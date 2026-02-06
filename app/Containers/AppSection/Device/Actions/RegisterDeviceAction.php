<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Data\Repositories\DeviceRepository;
use App\Containers\AppSection\Device\Tasks\FindDeviceKeyByKeyIdTask;
use App\Containers\AppSection\Device\Tasks\UpdateOrCreateDeviceKeyTask;
use App\Containers\AppSection\Device\Tasks\UpdateOrCreateDeviceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RegisterDeviceAction extends ParentAction
{
    public function __construct(
        private readonly UpdateOrCreateDeviceTask $updateOrCreateDeviceTask,
        private readonly UpdateOrCreateDeviceKeyTask $updateOrCreateDeviceKeyTask,
        private readonly FindDeviceKeyByKeyIdTask $findDeviceKeyByKeyIdTask,
        private readonly DeviceRepository $deviceRepository,
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

            $this->assertValidPublicKey($publicKey);

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
                $deviceUpdates['push_token_hash'] = $this->hashPushToken($payload['push_token'] ?? null);
            }

            if (!empty($deviceUpdates['push_token_hash']) && !empty($deviceUpdates['push_provider'])) {
                $this->deviceRepository->getModel()->newQuery()
                    ->where('push_provider', $deviceUpdates['push_provider'])
                    ->where('push_token_hash', $deviceUpdates['push_token_hash'])
                    ->update([
                        'push_token' => null,
                        'push_token_hash' => null,
                    ]);
            }

            $device = $this->updateOrCreateDeviceTask->run($deviceAttributes, $deviceUpdates);

            $existingKey = $this->findDeviceKeyByKeyIdTask->run($keyId);

            if ($existingKey && $existingKey->device_id !== $device->id) {
                throw ValidationException::withMessages([
                    'key_id' => ['The key_id has already been taken.'],
                ]);
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

            return [
                'device' => $device,
                'key' => $key,
            ];
        });
    }

    private function hashPushToken(string|null $token): string|null
    {
        if ($token === null || $token === '') {
            return null;
        }

        return hash('sha256', $token);
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
