<?php

namespace App\Containers\AppSection\Device\Contracts;

use Carbon\CarbonImmutable;

interface TouchDeviceSignatureActivity
{
    public function run(int $deviceKeyId, int $deviceId, CarbonImmutable $occurredAt): void;
}
