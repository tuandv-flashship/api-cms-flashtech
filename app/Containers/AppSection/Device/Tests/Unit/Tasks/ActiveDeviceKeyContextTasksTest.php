<?php

namespace App\Containers\AppSection\Device\Tests\Unit\Tasks;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Tasks\FindActiveDeviceKeyContextTask;
use App\Containers\AppSection\Device\Tasks\IsActiveDeviceKeyContextTask;
use App\Containers\AppSection\Device\Tests\UnitTestCase;
use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Supports\Base64Url;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FindActiveDeviceKeyContextTask::class)]
#[CoversClass(IsActiveDeviceKeyContextTask::class)]
final class ActiveDeviceKeyContextTasksTest extends UnitTestCase
{
    public function testFindAndCheckReturnContextForActiveDeviceKey(): void
    {
        $member = Member::factory()->createOne();

        $device = Device::query()->create([
            'owner_type' => DeviceOwnerType::MEMBER,
            'owner_id' => $member->id,
            'device_id' => 'ctx-active-device',
            'status' => DeviceStatus::ACTIVE,
        ]);

        $key = DeviceKey::query()->create([
            'device_id' => $device->id,
            'key_id' => Base64Url::encode(random_bytes(12)),
            'public_key' => Base64Url::encode(random_bytes(32)),
            'status' => DeviceKey::STATUS_ACTIVE,
        ]);

        $findTask = app(FindActiveDeviceKeyContextTask::class);
        $checkTask = app(IsActiveDeviceKeyContextTask::class);

        $context = $findTask->run($key->key_id);

        $this->assertNotNull($context);
        $this->assertSame($key->id, $context->deviceKeyId);
        $this->assertSame($device->id, $context->deviceId);
        $this->assertSame($key->public_key, $context->publicKey);
        $this->assertSame(DeviceOwnerType::MEMBER->value, $context->ownerType);
        $this->assertSame($member->id, $context->ownerId);

        $this->assertTrue($checkTask->run($key->key_id, $key->id, $device->id));
    }

    public function testFindAndCheckRejectRevokedDeviceOrKey(): void
    {
        $member = Member::factory()->createOne();

        $revokedDevice = Device::query()->create([
            'owner_type' => DeviceOwnerType::MEMBER,
            'owner_id' => $member->id,
            'device_id' => 'ctx-revoked-device',
            'status' => DeviceStatus::REVOKED,
        ]);

        $revokedDeviceKey = DeviceKey::query()->create([
            'device_id' => $revokedDevice->id,
            'key_id' => Base64Url::encode(random_bytes(12)),
            'public_key' => Base64Url::encode(random_bytes(32)),
            'status' => DeviceKey::STATUS_ACTIVE,
        ]);

        $activeDevice = Device::query()->create([
            'owner_type' => DeviceOwnerType::MEMBER,
            'owner_id' => $member->id,
            'device_id' => 'ctx-active-device-2',
            'status' => DeviceStatus::ACTIVE,
        ]);

        $revokedKey = DeviceKey::query()->create([
            'device_id' => $activeDevice->id,
            'key_id' => Base64Url::encode(random_bytes(12)),
            'public_key' => Base64Url::encode(random_bytes(32)),
            'status' => DeviceKey::STATUS_REVOKED,
        ]);

        $findTask = app(FindActiveDeviceKeyContextTask::class);
        $checkTask = app(IsActiveDeviceKeyContextTask::class);

        $this->assertNull($findTask->run($revokedDeviceKey->key_id));
        $this->assertNull($findTask->run($revokedKey->key_id));

        $this->assertFalse($checkTask->run($revokedDeviceKey->key_id, $revokedDeviceKey->id, $revokedDevice->id));
        $this->assertFalse($checkTask->run($revokedKey->key_id, $revokedKey->id, $activeDevice->id));
    }
}
