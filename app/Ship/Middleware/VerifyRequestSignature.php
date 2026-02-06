<?php

namespace App\Ship\Middleware;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Models\DeviceKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class VerifyRequestSignature
{
    private const DEFAULT_HEADERS = [
        'header_key_id' => 'x-key-id',
        'header_signature' => 'x-signature',
        'header_timestamp' => 'x-timestamp',
        'header_nonce' => 'x-nonce',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!config('device.signature.enabled', false)) {
            return $next($request);
        }

        $keyIdHeader = $this->headerName('header_key_id');
        $signatureHeader = $this->headerName('header_signature');
        $timestampHeader = $this->headerName('header_timestamp');
        $nonceHeader = $this->headerName('header_nonce');

        $keyId = $request->header($keyIdHeader);
        $signature = $request->header($signatureHeader);
        $timestamp = $request->header($timestampHeader);
        $nonce = $request->header($nonceHeader);

        if (!$signature || !$keyId || !$timestamp || !$nonce) {
            if (!config('device.signature.enforce', false)) {
                return $next($request);
            }

            return $this->reject('Missing signature headers.');
        }

        if (!ctype_digit((string) $timestamp)) {
            return $this->reject('Invalid signature timestamp.');
        }

        $timestampInt = (int) $timestamp;
        $allowedSkew = (int) config('device.signature.timestamp_ttl', 300);
        if (abs(time() - $timestampInt) > $allowedSkew) {
            return $this->reject('Signature timestamp expired.');
        }

        if (!$this->storeNonce((string) $keyId, (string) $nonce)) {
            return $this->reject('Signature nonce already used.');
        }

        $deviceKey = DeviceKey::query()
            ->where('key_id', (string) $keyId)
            ->where('status', DeviceKey::STATUS_ACTIVE)
            ->first();

        if (!$deviceKey || !$deviceKey->device || $deviceKey->device->status !== DeviceStatus::ACTIVE) {
            return $this->reject('Invalid signature key.');
        }

        if (!$this->matchesAuthenticatedOwner($request, $deviceKey->device)) {
            return $this->reject('Signature key does not match owner.');
        }

        $payload = $this->canonicalPayload($request, $timestampInt, (string) $nonce);

        if (!$this->verifySignature((string) $signature, $payload, $deviceKey->public_key)) {
            return $this->reject('Invalid request signature.');
        }

        $deviceKey->forceFill(['last_used_at' => now()])->save();
        $deviceKey->device->forceFill(['last_seen_at' => now()])->save();
        $request->attributes->set('device_key', $deviceKey);

        return $next($request);
    }

    private function headerName(string $configKey): string
    {
        $value = (string) config("device.signature.{$configKey}", '');

        return $value !== '' ? $value : (self::DEFAULT_HEADERS[$configKey] ?? '');
    }

    private function canonicalPayload(Request $request, int $timestamp, string $nonce): string
    {
        $method = strtoupper($request->method());
        $path = $request->getPathInfo();
        $query = $this->canonicalQuery($request->query());
        $bodyHash = hash('sha256', (string) $request->getContent());

        return implode("\n", [
            $method,
            $path,
            $query,
            $bodyHash,
            (string) $timestamp,
            $nonce,
        ]);
    }

    private function canonicalQuery(array $query): string
    {
        if ($query === []) {
            return '';
        }

        $sorted = Arr::sortRecursive($query);

        return http_build_query($sorted, '', '&', PHP_QUERY_RFC3986);
    }

    private function storeNonce(string $keyId, string $nonce): bool
    {
        $ttl = (int) config('device.signature.nonce_ttl', 300);
        $cacheKey = sprintf('sig_nonce:%s:%s', $keyId, $nonce);

        return Cache::add($cacheKey, 1, now()->addSeconds($ttl));
    }

    private function verifySignature(string $signature, string $payload, string $publicKey): bool
    {
        if (config('device.signature.algorithm', 'ed25519') !== 'ed25519') {
            return false;
        }

        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            return false;
        }

        $decodedSignature = $this->base64UrlDecode($signature);
        $decodedPublicKey = $this->base64UrlDecode($publicKey);

        if ($decodedSignature === null || $decodedPublicKey === null) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($decodedSignature, $payload, $decodedPublicKey);
    }

    //Đảm bảo x-signature và public_key (Base64URL) được decode đúng để verify chữ ký Ed25519.
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

    private function matchesAuthenticatedOwner(Request $request, Device $device): bool
    {
        if (auth('member')->check()) {
            return $device->owner_type === DeviceOwnerType::MEMBER
                && $device->owner_id === (int) auth('member')->id();
        }

        if (auth('api')->check()) {
            return $device->owner_type === DeviceOwnerType::USER
                && $device->owner_id === (int) auth('api')->id();
        }

        return true;
    }

    private function reject(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error_code' => 'invalid_signature',
        ], 401);
    }
}
