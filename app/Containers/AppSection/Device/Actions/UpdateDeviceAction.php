<?php

namespace App\Containers\AppSection\Device\Actions;

use App\Containers\AppSection\Device\Data\Repositories\DeviceRepository;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Tasks\FindDeviceByOwnerTask;
use App\Containers\AppSection\Device\Tasks\UpdateDeviceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateDeviceAction extends ParentAction
{
    public function __construct(
        private readonly FindDeviceByOwnerTask $findDeviceByOwnerTask,
        private readonly UpdateDeviceTask $updateDeviceTask,
        private readonly DeviceRepository $deviceRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function run(DeviceOwnerType $ownerType, int $ownerId, string $deviceId, array $payload): Device
    {
        return DB::transaction(function () use ($ownerType, $ownerId, $deviceId, $payload): Device {
            $device = $this->findDeviceByOwnerTask->run($ownerType, $ownerId, $deviceId);

            $updates = [];
            $fields = [
                'platform',
                'device_name',
                'push_token',
                'push_provider',
                'app_version',
            ];

            foreach ($fields as $field) {
                if (array_key_exists($field, $payload)) {
                    $updates[$field] = $payload[$field];
                }
            }

            if (array_key_exists('push_token', $payload)) {
                $updates['push_token_hash'] = $this->hashPushToken($payload['push_token'] ?? null);
            }

            if (!empty($updates['push_token_hash']) && !empty($updates['push_provider'])) {
                $this->deviceRepository->getModel()->newQuery()
                    ->where('push_provider', $updates['push_provider'])
                    ->where('push_token_hash', $updates['push_token_hash'])
                    ->where('id', '<>', $device->id)
                    ->update([
                        'push_token' => null,
                        'push_token_hash' => null,
                    ]);
            }

            $updates['last_seen_at'] = now();

            if ($updates !== []) {
                $device = $this->updateDeviceTask->run($device->id, $updates);
            }

            return $device;
        });
    }

    private function hashPushToken(string|null $token): string|null
    {
        if ($token === null || $token === '') {
            return null;
        }

        return hash('sha256', $token);
    }
}
