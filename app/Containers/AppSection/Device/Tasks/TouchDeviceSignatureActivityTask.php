<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Contracts\TouchDeviceSignatureActivity;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Carbon\CarbonImmutable;

final class TouchDeviceSignatureActivityTask extends ParentTask implements TouchDeviceSignatureActivity
{
    public function run(int $deviceKeyId, int $deviceId, CarbonImmutable $occurredAt): void
    {
        DeviceKey::query()
            ->whereKey($deviceKeyId)
            ->where(static function ($query) use ($occurredAt): void {
                $query
                    ->whereNull('last_used_at')
                    ->orWhere('last_used_at', '<', $occurredAt);
            })
            ->update(['last_used_at' => $occurredAt]);

        Device::query()
            ->whereKey($deviceId)
            ->where(static function ($query) use ($occurredAt): void {
                $query
                    ->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', $occurredAt);
            })
            ->update(['last_seen_at' => $occurredAt]);
    }
}
