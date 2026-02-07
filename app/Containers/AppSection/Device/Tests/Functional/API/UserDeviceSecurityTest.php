<?php

namespace App\Containers\AppSection\Device\Tests\Functional\API;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Cache;

final class UserDeviceSecurityTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function testPrivateDeviceRouteRequiresApiAuth(): void
    {
        $response = $this->getJson(route('api_user_list_device_keys', ['device_id' => 'device-auth-required']));

        $response->assertStatus(401);
    }

    public function testPrivateUserDeviceListRouteRequiresApiAuth(): void
    {
        $response = $this->getJson(route('api_user_list_devices'));

        $response->assertStatus(401);
    }

    public function testAuthenticatedUserDeviceListRouteResolvesDeviceEndpoint(): void
    {
        $user = User::factory()->createOne();
        $deviceId = $this->createUserDeviceAndKey($user);

        $response = $this->actingAs($user, 'api')->getJson(route('api_user_list_devices'));

        $response->assertOk();
        $response->assertJsonPath('data.0.device_id', $deviceId);
    }

    public function testUserUpdateDeviceRejectsMissingSignatureHeaders(): void
    {
        config([
            'device.signature.enabled' => true,
            'device.signature.enforce' => true,
        ]);

        $user = User::factory()->createOne();

        $response = $this->actingAs($user, 'api')->patchJson(
            route('api_user_update_device', ['device_id' => 'user-signature-missing']),
            [],
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_headers_missing');
    }

    public function testUserListDeviceKeysIsThrottled(): void
    {
        $user = User::factory()->createOne();
        $deviceId = $this->createUserDeviceAndKey($user);

        $uri = route('api_user_list_device_keys', ['device_id' => $deviceId]);
        for ($index = 0; $index < 30; $index++) {
            $response = $this->actingAs($user, 'api')->getJson($uri);
            $response->assertOk();
        }

        $rateLimitedResponse = $this->actingAs($user, 'api')->getJson($uri);
        $rateLimitedResponse->assertStatus(429);
    }

    private function createUserDeviceAndKey(User $user): string
    {
        $deviceId = 'throttle-user-device';

        $device = Device::query()->create([
            'owner_type' => DeviceOwnerType::USER,
            'owner_id' => $user->id,
            'device_id' => $deviceId,
            'status' => DeviceStatus::ACTIVE,
        ]);

        DeviceKey::query()->create([
            'device_id' => $device->id,
            'key_id' => $this->base64UrlEncode(random_bytes(12)),
            'public_key' => $this->base64UrlEncode(random_bytes(32)),
            'status' => DeviceKey::STATUS_ACTIVE,
        ]);

        return $deviceId;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
