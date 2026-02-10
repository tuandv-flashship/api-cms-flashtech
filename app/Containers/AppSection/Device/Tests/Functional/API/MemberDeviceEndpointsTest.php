<?php

namespace App\Containers\AppSection\Device\Tests\Functional\API;

use App\Containers\AppSection\Device\Supports\DeviceSignatureCacheKey;
use App\Containers\AppSection\Device\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Member\Models\Member;
use Illuminate\Support\Facades\Cache;

final class MemberDeviceEndpointsTest extends ApiTestCase
{
    public function testMemberDeviceLifecycle(): void
    {
        $member = Member::factory()->create();

        $deviceId = 'device-1';
        $keyId = $this->randomKeyId();
        $publicKey = $this->randomPublicKey();

        $registerResponse = $this->actingAs($member, 'member')->postJson(
            route('api_member_register_device'),
            [
                'device_id' => $deviceId,
                'key_id' => $keyId,
                'public_key' => $publicKey,
            ],
        );

        $registerResponse->assertOk();
        $registerResponse->assertJsonPath('data.device_id', $deviceId);
        $registerResponse->assertJsonPath('meta.key_id', $keyId);

        $listResponse = $this->actingAs($member, 'member')->getJson(
            route('api_member_list_devices'),
        );
        $listResponse->assertOk();
        $listResponse->assertJsonCount(1, 'data');

        $updateResponse = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => $deviceId]),
            [
                'push_token' => 'token-1',
                'push_provider' => 'fcm',
            ],
        );
        $updateResponse->assertOk();

        $keysResponse = $this->actingAs($member, 'member')->getJson(
            route('api_member_list_device_keys', [
                'device_id' => $deviceId,
                'include_public_key' => 1,
            ]),
        );
        $keysResponse->assertOk();
        $this->assertArrayNotHasKey('public_key', $keysResponse->json('data.0'));

        $newKeyId = $this->randomKeyId();
        $newPublicKey = $this->randomPublicKey();

        $rotateResponse = $this->actingAs($member, 'member')->postJson(
            route('api_member_rotate_device_key', ['device_id' => $deviceId]),
            [
                'key_id' => $newKeyId,
                'public_key' => $newPublicKey,
            ],
        );
        $rotateResponse->assertOk();
        $rotateResponse->assertJsonPath('data.key_id', $newKeyId);

        $this->assertDatabaseHas('device_keys', ['key_id' => $newKeyId, 'status' => 'active']);
        $this->assertDatabaseHas('device_keys', ['key_id' => $keyId, 'status' => 'revoked']);

        $revokeKeyResponse = $this->actingAs($member, 'member')->deleteJson(
            route('api_member_revoke_device_key', [
                'device_id' => $deviceId,
                'key_id' => $newKeyId,
            ]),
        );
        $revokeKeyResponse->assertOk();
        $revokeKeyResponse->assertJsonPath('data.status', 'revoked');
        $this->assertDatabaseHas('device_keys', ['key_id' => $newKeyId, 'status' => 'revoked']);

        $revokeDeviceResponse = $this->actingAs($member, 'member')->deleteJson(
            route('api_member_revoke_device', ['device_id' => $deviceId]),
        );
        $revokeDeviceResponse->assertOk();
        $revokeDeviceResponse->assertJsonPath('data.status', 'revoked');
        $this->assertDatabaseHas('devices', ['device_id' => $deviceId, 'status' => 'revoked']);
    }

    public function testMemberCanIncludeKeysOnList(): void
    {
        $member = Member::factory()->create();

        $deviceId = 'device-include-keys';
        $keyId = $this->randomKeyId();
        $publicKey = $this->randomPublicKey();

        $registerResponse = $this->actingAs($member, 'member')->postJson(
            route('api_member_register_device'),
            [
                'device_id' => $deviceId,
                'key_id' => $keyId,
                'public_key' => $publicKey,
            ],
        );
        $registerResponse->assertOk();

        $listResponse = $this->actingAs($member, 'member')->getJson(
            route('api_member_list_devices', ['include' => 'keys']),
        );

        $listResponse->assertOk();
        $this->assertSame($deviceId, $listResponse->json('data.0.device_id'));
        $this->assertSame($keyId, $listResponse->json('data.0.keys.data.0.key_id'));
        $this->assertNull($listResponse->json('data.0.keys.data.0.public_key'));
    }

    public function testMutatingKeyEndpointsInvalidateSignatureContextCache(): void
    {
        Cache::flush();

        $member = Member::factory()->create();

        $deviceId = 'device-cache-invalidate';
        $oldKeyId = $this->randomKeyId();
        $oldPublicKey = $this->randomPublicKey();
        $newKeyId = $this->randomKeyId();
        $newPublicKey = $this->randomPublicKey();

        Cache::put(DeviceSignatureCacheKey::keyContext($oldKeyId), ['stale' => true], now()->addMinutes(5));

        $registerResponse = $this->actingAs($member, 'member')->postJson(
            route('api_member_register_device'),
            [
                'device_id' => $deviceId,
                'key_id' => $oldKeyId,
                'public_key' => $oldPublicKey,
            ],
        );
        $registerResponse->assertOk();
        $this->assertFalse(Cache::has(DeviceSignatureCacheKey::keyContext($oldKeyId)));

        Cache::put(DeviceSignatureCacheKey::keyContext($oldKeyId), ['stale' => true], now()->addMinutes(5));
        Cache::put(DeviceSignatureCacheKey::keyContext($newKeyId), ['stale' => true], now()->addMinutes(5));

        $rotateResponse = $this->actingAs($member, 'member')->postJson(
            route('api_member_rotate_device_key', ['device_id' => $deviceId]),
            [
                'key_id' => $newKeyId,
                'public_key' => $newPublicKey,
            ],
        );
        $rotateResponse->assertOk();
        $this->assertFalse(Cache::has(DeviceSignatureCacheKey::keyContext($oldKeyId)));
        $this->assertFalse(Cache::has(DeviceSignatureCacheKey::keyContext($newKeyId)));

        Cache::put(DeviceSignatureCacheKey::keyContext($newKeyId), ['stale' => true], now()->addMinutes(5));

        $revokeKeyResponse = $this->actingAs($member, 'member')->deleteJson(
            route('api_member_revoke_device_key', [
                'device_id' => $deviceId,
                'key_id' => $newKeyId,
            ]),
        );
        $revokeKeyResponse->assertOk();
        $this->assertFalse(Cache::has(DeviceSignatureCacheKey::keyContext($newKeyId)));
    }

    public function testMemberRotateDeviceKeyReturnsErrorCodeWhenPublicKeyInvalid(): void
    {
        $member = Member::factory()->create();

        $deviceId = 'member-invalid-key-device';
        $keyId = $this->randomKeyId();
        $publicKey = $this->randomPublicKey();

        $registerResponse = $this->actingAs($member, 'member')->postJson(
            route('api_member_register_device'),
            [
                'device_id' => $deviceId,
                'key_id' => $keyId,
                'public_key' => $publicKey,
            ],
        );
        $registerResponse->assertOk();

        $rotateResponse = $this->actingAs($member, 'member')->postJson(
            route('api_member_rotate_device_key', ['device_id' => $deviceId]),
            [
                'key_id' => $this->randomKeyId(),
                'public_key' => 'a',
            ],
        );

        $rotateResponse->assertStatus(422);
        $rotateResponse->assertJsonPath('message', 'The public_key is invalid.');
        $rotateResponse->assertJsonPath('error_code', 'invalid_public_key');
    }

    public function testMemberRevokeDeviceKeyReturnsErrorCodeWhenKeyNotFound(): void
    {
        $member = Member::factory()->create();

        $deviceId = 'member-revoke-missing-key';
        $keyId = $this->randomKeyId();
        $publicKey = $this->randomPublicKey();

        $registerResponse = $this->actingAs($member, 'member')->postJson(
            route('api_member_register_device'),
            [
                'device_id' => $deviceId,
                'key_id' => $keyId,
                'public_key' => $publicKey,
            ],
        );
        $registerResponse->assertOk();

        $response = $this->actingAs($member, 'member')->deleteJson(
            route('api_member_revoke_device_key', [
                'device_id' => $deviceId,
                'key_id' => 'missing-key-id',
            ]),
        );

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Device key not found.');
        $response->assertJsonPath('error_code', 'device_key_not_found');
    }

    private function randomKeyId(): string
    {
        return $this->base64UrlEncode(random_bytes(12));
    }

    private function randomPublicKey(): string
    {
        $length = defined('SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES')
            ? SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
            : 32;

        return $this->base64UrlEncode(random_bytes($length));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
