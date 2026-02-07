<?php

namespace App\Containers\AppSection\Device\Tests\Functional\API;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Jobs\UpdateDeviceSignatureActivityJob;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Supports\DeviceSignatureCacheKey;
use App\Containers\AppSection\Device\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Supports\Base64Url;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

final class RequestSignatureMiddlewareTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'device.signature.enabled' => true,
            'device.signature.enforce' => true,
            'device.signature.algorithm' => 'ed25519',
            'device.signature.nonce_ttl' => 300,
            'device.signature.timestamp_ttl' => 300,
        ]);

        Cache::flush();
    }

    public function testRejectsMissingSignatureHeaders(): void
    {
        $member = Member::factory()->create();

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-1']),
            [],
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_headers_missing');
    }

    public function testRejectsInvalidTimestamp(): void
    {
        $member = Member::factory()->create();

        $headers = $this->signatureHeaders(
            keyId: $this->randomKeyId(),
            timestamp: 'invalid',
        );

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-2']),
            [],
            $headers,
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_timestamp_invalid');
    }

    public function testRejectsInvalidKeyIdHeader(): void
    {
        $member = Member::factory()->create();

        $headers = $this->signatureHeaders(
            keyId: 'invalid$key-id',
        );

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-invalid-key-id-header']),
            [],
            $headers,
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_key_id_invalid');
    }

    public function testRejectsInvalidNonceHeader(): void
    {
        $member = Member::factory()->create();

        $headers = $this->signatureHeaders(
            keyId: $this->randomKeyId(),
            nonce: 'bad.nonce',
        );

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-invalid-nonce-header']),
            [],
            $headers,
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_nonce_invalid');
    }

    public function testRejectsInvalidSignatureHeader(): void
    {
        $member = Member::factory()->create();

        $headers = $this->signatureHeaders(
            keyId: $this->randomKeyId(),
            signature: 'short',
        );

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-invalid-signature-header']),
            [],
            $headers,
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_header_signature_invalid');
    }

    public function testRejectsInvalidKey(): void
    {
        $member = Member::factory()->create();

        $headers = $this->signatureHeaders(
            keyId: $this->randomKeyId(),
        );

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-3']),
            [],
            $headers,
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_key_invalid');
    }

    public function testRejectsOwnerMismatch(): void
    {
        $member = Member::factory()->create();
        $anotherMember = Member::factory()->create();

        $key = $this->createActiveMemberDeviceKey($anotherMember, 'sig-device-owner-mismatch');

        $headers = $this->signatureHeaders(
            keyId: $key->key_id,
        );

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-4']),
            [],
            $headers,
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_owner_mismatch');
    }

    public function testOwnerMismatchDoesNotConsumeNonceCache(): void
    {
        $member = Member::factory()->create();
        $anotherMember = Member::factory()->create();

        $key = $this->createActiveMemberDeviceKey($anotherMember, 'sig-device-owner-mismatch-nonce');
        $nonce = $this->randomNonce();
        $headers = $this->signatureHeaders(
            keyId: $key->key_id,
            nonce: $nonce,
            signature: Base64Url::encode(random_bytes(64)),
        );

        $firstResponse = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-owner-mismatch-nonce']),
            [],
            $headers,
        );
        $secondResponse = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-owner-mismatch-nonce']),
            [],
            $headers,
        );

        $firstResponse->assertStatus(401);
        $firstResponse->assertJsonPath('error_code', 'signature_owner_mismatch');
        $secondResponse->assertStatus(401);
        $secondResponse->assertJsonPath('error_code', 'signature_owner_mismatch');
    }

    public function testRejectsInvalidSignature(): void
    {
        $member = Member::factory()->create();
        $key = $this->createActiveMemberDeviceKey($member, 'sig-device-invalid-signature');

        $headers = $this->signatureHeaders(
            keyId: $key->key_id,
            signature: Base64Url::encode(random_bytes(64)),
        );

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-invalid-signature']),
            [],
            $headers,
        );

        $response->assertStatus(401);
        $response->assertJsonPath('error_code', 'signature_invalid');
    }

    public function testRejectsReusedNonce(): void
    {
        $member = Member::factory()->create();
        $key = $this->createActiveMemberDeviceKey($member, 'sig-device-reused-nonce');
        $nonce = $this->randomNonce();

        $headers = $this->signatureHeaders(
            keyId: $key->key_id,
            nonce: $nonce,
            signature: Base64Url::encode(random_bytes(64)),
        );

        $firstResponse = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-reused-nonce']),
            [],
            $headers,
        );
        $firstResponse->assertStatus(401);
        $firstResponse->assertJsonPath('error_code', 'signature_invalid');

        $secondResponse = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-reused-nonce']),
            [],
            $headers,
        );
        $secondResponse->assertStatus(401);
        $secondResponse->assertJsonPath('error_code', 'signature_nonce_reused');
    }

    public function testRepeatedInvalidKeyDoesNotConsumeNonceCache(): void
    {
        $member = Member::factory()->create();
        $nonce = $this->randomNonce();
        $headers = $this->signatureHeaders(
            keyId: $this->randomKeyId(),
            nonce: $nonce,
        );

        $firstResponse = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-invalid-key-replay']),
            [],
            $headers,
        );

        $secondResponse = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => 'sig-device-invalid-key-replay']),
            [],
            $headers,
        );

        $firstResponse->assertStatus(401);
        $firstResponse->assertJsonPath('error_code', 'signature_key_invalid');
        $secondResponse->assertStatus(401);
        $secondResponse->assertJsonPath('error_code', 'signature_key_invalid');
    }

    public function testAllowsValidSignature(): void
    {
        if (!function_exists('sodium_crypto_sign_keypair') || !function_exists('sodium_crypto_sign_detached')) {
            $this->markTestSkipped('Sodium extension is required.');
        }

        $member = Member::factory()->create();
        $keyPair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keyPair);
        $publicKey = Base64Url::encode(sodium_crypto_sign_publickey($keyPair));

        $deviceId = 'sig-device-valid';
        $key = $this->createActiveMemberDeviceKey($member, $deviceId, $publicKey);

        $payload = ['app_version' => '1.2.3'];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = time();
        $nonce = $this->randomNonce();
        $path = parse_url(
            route('api_member_update_device', ['device_id' => $deviceId]),
            PHP_URL_PATH,
        ) ?: '';

        $canonicalPayload = $this->canonicalPayload(
            method: 'PATCH',
            path: $path,
            query: [],
            body: $body,
            timestamp: $timestamp,
            nonce: $nonce,
        );

        $signature = Base64Url::encode(sodium_crypto_sign_detached($canonicalPayload, $secretKey));

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => $deviceId]),
            $payload,
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: $signature,
                timestamp: (string) $timestamp,
                nonce: $nonce,
            ),
        );

        $response->assertOk();
        $response->assertJsonPath('data.device_id', $deviceId);
    }

    public function testAllowsValidSignatureWhenKeyContextCacheDisabled(): void
    {
        if (!function_exists('sodium_crypto_sign_keypair') || !function_exists('sodium_crypto_sign_detached')) {
            $this->markTestSkipped('Sodium extension is required.');
        }

        config([
            'device.signature.key_context_cache_enabled' => false,
        ]);

        $member = Member::factory()->create();
        $keyPair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keyPair);
        $publicKey = Base64Url::encode(sodium_crypto_sign_publickey($keyPair));

        $deviceId = 'sig-device-valid-no-cache';
        $key = $this->createActiveMemberDeviceKey($member, $deviceId, $publicKey);

        // This entry should be ignored because cache is disabled.
        Cache::put(
            DeviceSignatureCacheKey::keyContext($key->key_id),
            [
                'device_key_id' => $key->id,
                'device_id' => $key->device_id,
                'public_key' => Base64Url::encode(random_bytes(32)),
                'owner_type' => DeviceOwnerType::MEMBER->value,
                'owner_id' => $member->id,
            ],
            now()->addMinutes(5),
        );

        $payload = ['app_version' => '9.9.9'];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = time();
        $nonce = $this->randomNonce();
        $path = parse_url(
            route('api_member_update_device', ['device_id' => $deviceId]),
            PHP_URL_PATH,
        ) ?: '';

        $canonicalPayload = $this->canonicalPayload(
            method: 'PATCH',
            path: $path,
            query: [],
            body: $body,
            timestamp: $timestamp,
            nonce: $nonce,
        );

        $signature = Base64Url::encode(sodium_crypto_sign_detached($canonicalPayload, $secretKey));

        $response = $this->actingAs($member, 'member')->patchJson(
            route('api_member_update_device', ['device_id' => $deviceId]),
            $payload,
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: $signature,
                timestamp: (string) $timestamp,
                nonce: $nonce,
            ),
        );

        $response->assertOk();
    }

    public function testRejectsRevokedKeyEvenWhenCachedContextExists(): void
    {
        if (!function_exists('sodium_crypto_sign_keypair') || !function_exists('sodium_crypto_sign_detached')) {
            $this->markTestSkipped('Sodium extension is required.');
        }

        config([
            'device.signature.key_context_cache_enabled' => true,
            'device.signature.key_context_consistency_check' => true,
        ]);

        $member = Member::factory()->create();
        $keyPair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keyPair);
        $publicKey = Base64Url::encode(sodium_crypto_sign_publickey($keyPair));

        $deviceId = 'sig-device-revoke-cache';
        $key = $this->createActiveMemberDeviceKey($member, $deviceId, $publicKey);
        $route = route('api_member_update_device', ['device_id' => $deviceId]);
        $path = parse_url($route, PHP_URL_PATH) ?: '';
        $payload = ['app_version' => '1.0.1'];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $firstTimestamp = time();
        $firstNonce = $this->randomNonce();
        $firstSignature = Base64Url::encode(sodium_crypto_sign_detached(
            $this->canonicalPayload('PATCH', $path, [], $body, $firstTimestamp, $firstNonce),
            $secretKey,
        ));

        $firstResponse = $this->actingAs($member, 'member')->patchJson(
            $route,
            $payload,
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: $firstSignature,
                timestamp: (string) $firstTimestamp,
                nonce: $firstNonce,
            ),
        );
        $firstResponse->assertOk();

        // Revoke key directly after context has been cached.
        $key->forceFill(['status' => DeviceKey::STATUS_REVOKED])->save();

        $secondTimestamp = $firstTimestamp + 1;
        $secondNonce = $this->randomNonce();
        $secondSignature = Base64Url::encode(sodium_crypto_sign_detached(
            $this->canonicalPayload('PATCH', $path, [], $body, $secondTimestamp, $secondNonce),
            $secretKey,
        ));

        $secondResponse = $this->actingAs($member, 'member')->patchJson(
            $route,
            $payload,
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: $secondSignature,
                timestamp: (string) $secondTimestamp,
                nonce: $secondNonce,
            ),
        );

        $secondResponse->assertStatus(401);
        $secondResponse->assertJsonPath('error_code', 'signature_key_invalid');
    }

    public function testThrottlesRepeatedInvalidSignatureFailuresByKeyAndIp(): void
    {
        if (!function_exists('sodium_crypto_sign_keypair') || !function_exists('sodium_crypto_sign_detached')) {
            $this->markTestSkipped('Sodium extension is required.');
        }

        config([
            'device.signature.failure_throttle_enabled' => true,
            'device.signature.failure_throttle_max_attempts' => 2,
            'device.signature.failure_throttle_decay_seconds' => 300,
            'device.signature.failure_throttle_error_codes' => 'signature_invalid,signature_nonce_reused',
        ]);

        $member = Member::factory()->create();
        $keyPair = sodium_crypto_sign_keypair();
        $publicKey = Base64Url::encode(sodium_crypto_sign_publickey($keyPair));
        $deviceId = 'sig-device-throttle-invalid-signature';
        $key = $this->createActiveMemberDeviceKey($member, $deviceId, $publicKey);
        $route = route('api_member_update_device', ['device_id' => $deviceId]);

        $responseOne = $this->actingAs($member, 'member')->patchJson(
            $route,
            ['app_version' => '1.0.0'],
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: Base64Url::encode(random_bytes(64)),
                nonce: $this->randomNonce(),
            ),
        );
        $responseOne->assertStatus(401);
        $responseOne->assertJsonPath('error_code', 'signature_invalid');

        $responseTwo = $this->actingAs($member, 'member')->patchJson(
            $route,
            ['app_version' => '1.0.1'],
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: Base64Url::encode(random_bytes(64)),
                nonce: $this->randomNonce(),
            ),
        );
        $responseTwo->assertStatus(429);
        $responseTwo->assertJsonPath('error_code', 'signature_failure_throttled');

        $responseThree = $this->actingAs($member, 'member')->patchJson(
            $route,
            ['app_version' => '1.0.2'],
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: Base64Url::encode(random_bytes(64)),
                nonce: $this->randomNonce(),
            ),
        );
        $responseThree->assertStatus(429);
        $responseThree->assertJsonPath('error_code', 'signature_failure_throttled');
    }

    public function testDoesNotThrottleOwnerMismatchWhenErrorCodeNotConfigured(): void
    {
        config([
            'device.signature.failure_throttle_enabled' => true,
            'device.signature.failure_throttle_max_attempts' => 1,
            'device.signature.failure_throttle_decay_seconds' => 300,
            'device.signature.failure_throttle_error_codes' => 'signature_invalid,signature_nonce_reused',
        ]);

        $member = Member::factory()->create();
        $anotherMember = Member::factory()->create();
        $key = $this->createActiveMemberDeviceKey($anotherMember, 'sig-device-owner-mismatch-throttle');
        $route = route('api_member_update_device', ['device_id' => 'sig-device-owner-mismatch-throttle']);

        $responseOne = $this->actingAs($member, 'member')->patchJson(
            $route,
            [],
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: Base64Url::encode(random_bytes(64)),
                nonce: $this->randomNonce(),
            ),
        );
        $responseOne->assertStatus(401);
        $responseOne->assertJsonPath('error_code', 'signature_owner_mismatch');

        $responseTwo = $this->actingAs($member, 'member')->patchJson(
            $route,
            [],
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: Base64Url::encode(random_bytes(64)),
                nonce: $this->randomNonce(),
            ),
        );
        $responseTwo->assertStatus(401);
        $responseTwo->assertJsonPath('error_code', 'signature_owner_mismatch');
    }

    public function testDebouncesSignatureActivityUpdatesAndDispatchesJob(): void
    {
        if (!function_exists('sodium_crypto_sign_keypair') || !function_exists('sodium_crypto_sign_detached')) {
            $this->markTestSkipped('Sodium extension is required.');
        }

        config([
            'device.signature.activity_touch_debounce_seconds' => 300,
        ]);

        Queue::fake();

        $member = Member::factory()->create();
        $keyPair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keyPair);
        $publicKey = Base64Url::encode(sodium_crypto_sign_publickey($keyPair));

        $deviceId = 'sig-device-debounce';
        $key = $this->createActiveMemberDeviceKey($member, $deviceId, $publicKey);
        $route = route('api_member_update_device', ['device_id' => $deviceId]);
        $path = parse_url($route, PHP_URL_PATH) ?: '';
        $payload = ['app_version' => '2.0.0'];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $timestampOne = time();
        $nonceOne = $this->randomNonce();
        $signatureOne = Base64Url::encode(sodium_crypto_sign_detached(
            $this->canonicalPayload('PATCH', $path, [], $body, $timestampOne, $nonceOne),
            $secretKey,
        ));

        $responseOne = $this->actingAs($member, 'member')->patchJson(
            $route,
            $payload,
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: $signatureOne,
                timestamp: (string) $timestampOne,
                nonce: $nonceOne,
            ),
        );
        $responseOne->assertOk();

        $timestampTwo = $timestampOne + 1;
        $nonceTwo = $this->randomNonce();
        $signatureTwo = Base64Url::encode(sodium_crypto_sign_detached(
            $this->canonicalPayload('PATCH', $path, [], $body, $timestampTwo, $nonceTwo),
            $secretKey,
        ));

        $responseTwo = $this->actingAs($member, 'member')->patchJson(
            $route,
            $payload,
            $this->signatureHeaders(
                keyId: $key->key_id,
                signature: $signatureTwo,
                timestamp: (string) $timestampTwo,
                nonce: $nonceTwo,
            ),
        );
        $responseTwo->assertOk();

        Queue::assertPushed(UpdateDeviceSignatureActivityJob::class, 1);
    }

    private function createActiveMemberDeviceKey(
        Member $member,
        string $deviceId,
        string|null $publicKey = null,
    ): DeviceKey
    {
        $device = Device::query()->create([
            'owner_type' => DeviceOwnerType::MEMBER,
            'owner_id' => $member->id,
            'device_id' => $deviceId,
            'status' => DeviceStatus::ACTIVE,
        ]);

        return DeviceKey::query()->create([
            'device_id' => $device->id,
            'key_id' => $this->randomKeyId(),
            'public_key' => $publicKey ?? $this->randomPublicKey(),
            'status' => DeviceKey::STATUS_ACTIVE,
        ]);
    }

    /**
     * @param array<string, scalar|array<scalar>|null> $query
     */
    private function canonicalPayload(
        string $method,
        string $path,
        array $query,
        string $body,
        int $timestamp,
        string $nonce,
    ): string {
        $queryString = '';
        if ($query !== []) {
            $queryString = http_build_query(Arr::sortRecursive($query), '', '&', PHP_QUERY_RFC3986);
        }

        return implode("\n", [
            strtoupper($method),
            $path,
            $queryString,
            hash('sha256', $body),
            (string) $timestamp,
            $nonce,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function signatureHeaders(
        string $keyId,
        string|null $signature = null,
        string|null $timestamp = null,
        string|null $nonce = null,
    ): array {
        return [
            'x-key-id' => $keyId,
            'x-signature' => $signature ?? Base64Url::encode(random_bytes(64)),
            'x-timestamp' => $timestamp ?? (string) time(),
            'x-nonce' => $nonce ?? $this->randomNonce(),
        ];
    }

    private function randomNonce(): string
    {
        return Base64Url::encode(random_bytes(16));
    }

    private function randomKeyId(): string
    {
        return Base64Url::encode(random_bytes(12));
    }

    private function randomPublicKey(): string
    {
        $length = defined('SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES')
            ? SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
            : 32;

        return Base64Url::encode(random_bytes($length));
    }
}
