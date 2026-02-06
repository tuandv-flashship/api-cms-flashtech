<?php

namespace App\Containers\AppSection\Device\Tests\Functional\API;

use App\Containers\AppSection\Device\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\User\Models\User;

final class UserDeviceKeysTest extends ApiTestCase
{
    public function testAdminCanSeePublicKeyWhenRequested(): void
    {
        $user = User::factory()->createOne();

        $deviceId = 'admin-device-1';
        $keyId = $this->randomKeyId();
        $publicKey = $this->randomPublicKey();

        $registerResponse = $this->actingAs($user, 'api')->postJson(
            route('api_user_register_device'),
            [
                'device_id' => $deviceId,
                'key_id' => $keyId,
                'public_key' => $publicKey,
            ],
        );
        $registerResponse->assertOk();
        $registerResponse->assertJsonPath('meta.key_id', $keyId);

        $keysResponse = $this->actingAs($user, 'api')->getJson(
            route('api_user_list_device_keys', [
                'device_id' => $deviceId,
                'include_public_key' => 1,
            ]),
        );

        $keysResponse->assertOk();
        $this->assertSame($publicKey, $keysResponse->json('data.0.public_key'));
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
