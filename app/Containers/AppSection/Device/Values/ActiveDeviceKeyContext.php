<?php

namespace App\Containers\AppSection\Device\Values;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ActiveDeviceKeyContext extends ParentValue
{
    private function __construct(
        public int $deviceKeyId,
        public int $deviceId,
        public string $publicKey,
        public string $ownerType,
        public int $ownerId,
    ) {
    }

    public static function createFromRecord(object $record): self|null
    {
        return self::createFromArray([
            'device_key_id' => $record->device_key_id ?? null,
            'device_id' => $record->device_id ?? null,
            'public_key' => $record->public_key ?? null,
            'owner_type' => $record->owner_type ?? null,
            'owner_id' => $record->owner_id ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function createFromArray(array $payload): self|null
    {
        $deviceKeyId = (int) ($payload['device_key_id'] ?? 0);
        $deviceId = (int) ($payload['device_id'] ?? 0);
        $ownerId = (int) ($payload['owner_id'] ?? 0);
        $publicKey = (string) ($payload['public_key'] ?? '');
        $ownerType = (string) ($payload['owner_type'] ?? '');

        if ($deviceKeyId <= 0 || $deviceId <= 0 || $ownerId <= 0 || $publicKey === '' || $ownerType === '') {
            return null;
        }

        return new self(
            deviceKeyId: $deviceKeyId,
            deviceId: $deviceId,
            publicKey: $publicKey,
            ownerType: $ownerType,
            ownerId: $ownerId,
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'device_key_id' => $this->deviceKeyId,
            'device_id' => $this->deviceId,
            'public_key' => $this->publicKey,
            'owner_type' => $this->ownerType,
            'owner_id' => $this->ownerId,
        ];
    }
}
