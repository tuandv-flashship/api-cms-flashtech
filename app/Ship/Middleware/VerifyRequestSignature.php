<?php

namespace App\Ship\Middleware;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Containers\AppSection\Device\Jobs\UpdateDeviceSignatureActivityJob;
use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Containers\AppSection\Device\Supports\DeviceSignatureCacheKey;
use App\Ship\Supports\Base64Url;
use App\Ship\Values\ApiError;
use Closure;
use Illuminate\Cache\RateLimiter as CacheRateLimiter;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class VerifyRequestSignature
{
    private const DEFAULT_HEADERS = [
        'header_key_id' => 'x-key-id',
        'header_signature' => 'x-signature',
        'header_timestamp' => 'x-timestamp',
        'header_nonce' => 'x-nonce',
    ];

    private CacheRepository|null $signatureCacheRepository = null;
    private CacheRepository|null $signatureFailureThrottleCacheRepository = null;
    private CacheRepository|null $signatureMetricsCacheRepository = null;
    private CacheRateLimiter|null $signatureFailureRateLimiter = null;

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

            return $this->reject('Missing signature headers.', 'signature_headers_missing');
        }

        $headerValidationError = $this->validateHeaderFormats(
            (string) $keyId,
            (string) $signature,
            (string) $nonce,
        );
        if ($headerValidationError) {
            return $headerValidationError;
        }

        if (!ctype_digit((string) $timestamp)) {
            return $this->reject('Invalid signature timestamp.', 'signature_timestamp_invalid');
        }

        if ($this->isSignatureFailureThrottled($request, (string) $keyId)) {
            return $this->rejectSignatureFailureThrottled($request, (string) $keyId);
        }

        $timestampInt = (int) $timestamp;
        $allowedSkew = (int) config('device.signature.timestamp_ttl', 300);
        if (abs(time() - $timestampInt) > $allowedSkew) {
            return $this->reject('Signature timestamp expired.', 'signature_timestamp_expired');
        }

        $deviceKey = $this->findActiveDeviceKeyContext((string) $keyId);

        if ($deviceKey === null) {
            return $this->reject('Invalid signature key.', 'signature_key_invalid');
        }

        if (!$this->matchesAuthenticatedOwner(
            $request,
            (string) $deviceKey->owner_type,
            (int) $deviceKey->owner_id,
        )) {
            return $this->reject('Signature key does not match owner.', 'signature_owner_mismatch');
        }

        if (!$this->storeNonce((int) $deviceKey->device_key_id, (string) $nonce)) {
            return $this->reject('Signature nonce already used.', 'signature_nonce_reused');
        }

        $payload = $this->canonicalPayload($request, $timestampInt, (string) $nonce);

        if (!$this->verifySignature((string) $signature, $payload, (string) $deviceKey->public_key)) {
            return $this->reject('Invalid request signature.', 'signature_invalid');
        }

        $this->dispatchSignatureActivityUpdate(
            (int) $deviceKey->device_key_id,
            (int) $deviceKey->device_id,
        );
        $request->attributes->set('device_key_id', (int) $deviceKey->device_key_id);

        return $next($request);
    }

    private function findActiveDeviceKeyContext(string $keyId): object|null
    {
        if (!$this->shouldCacheKeyContext()) {
            return $this->queryActiveDeviceKeyContext($keyId);
        }

        $cacheKey = DeviceSignatureCacheKey::keyContext($keyId);
        $cachedContext = $this->signatureCache()->get($cacheKey);
        if (is_array($cachedContext)) {
            $cachedObject = (object) $cachedContext;
            if ($this->isCachedContextStale($cachedObject, $keyId)) {
                $this->signatureCache()->forget($cacheKey);

                return null;
            }

            return $cachedObject;
        }

        if (is_object($cachedContext)) {
            if ($this->isCachedContextStale($cachedContext, $keyId)) {
                $this->signatureCache()->forget($cacheKey);

                return null;
            }

            return $cachedContext;
        }

        $context = $this->queryActiveDeviceKeyContext($keyId);
        if ($context === null) {
            return null;
        }

        $ttlSeconds = max(1, (int) config('device.signature.key_context_cache_ttl', 60));

        $this->signatureCache()->put($cacheKey, (array) $context, now()->addSeconds($ttlSeconds));

        return $context;
    }

    private function isCachedContextStale(object $context, string $keyId): bool
    {
        if (!$this->shouldConsistencyCheckCachedContext()) {
            return false;
        }

        if (!isset($context->device_key_id, $context->device_id)) {
            return true;
        }

        return !DB::table('device_keys')
            ->join('devices', 'devices.id', '=', 'device_keys.device_id')
            ->where('device_keys.id', (int) $context->device_key_id)
            ->where('device_keys.device_id', (int) $context->device_id)
            ->where('device_keys.key_id', $keyId)
            ->where('device_keys.status', DeviceKey::STATUS_ACTIVE)
            ->where('devices.status', DeviceStatus::ACTIVE->value)
            ->exists();
    }

    private function shouldCacheKeyContext(): bool
    {
        return (bool) config('device.signature.key_context_cache_enabled', true);
    }

    private function shouldConsistencyCheckCachedContext(): bool
    {
        return (bool) config('device.signature.key_context_consistency_check', true);
    }

    private function queryActiveDeviceKeyContext(string $keyId): object|null
    {
        return DB::table('device_keys')
            ->join('devices', 'devices.id', '=', 'device_keys.device_id')
            ->where('device_keys.key_id', $keyId)
            ->where('device_keys.status', DeviceKey::STATUS_ACTIVE)
            ->where('devices.status', DeviceStatus::ACTIVE->value)
            ->select([
                'device_keys.id as device_key_id',
                'device_keys.device_id',
                'device_keys.public_key',
                'devices.owner_type',
                'devices.owner_id',
            ])
            ->first();
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

    private function storeNonce(int $deviceKeyId, string $nonce): bool
    {
        $ttl = (int) config('device.signature.nonce_ttl', 300);
        $cacheKey = DeviceSignatureCacheKey::nonce($deviceKeyId, $nonce);

        return $this->signatureCache()->add($cacheKey, 1, now()->addSeconds($ttl));
    }

    private function validateHeaderFormats(string $keyId, string $signature, string $nonce): JsonResponse|null
    {
        [$keyIdMin, $keyIdMax] = $this->headerLimit('key_id', 8, 191);
        if (!$this->isBase64UrlToken($keyId, $keyIdMin, $keyIdMax)) {
            return $this->reject('Invalid signature key header.', 'signature_key_id_invalid');
        }

        [$nonceMin, $nonceMax] = $this->headerLimit('nonce', 8, 128);
        if (!$this->isBase64UrlToken($nonce, $nonceMin, $nonceMax)) {
            return $this->reject('Invalid signature nonce header.', 'signature_nonce_invalid');
        }

        [$signatureMin, $signatureMax] = $this->headerLimit('signature', 20, 512);
        if (!$this->isBase64UrlToken($signature, $signatureMin, $signatureMax)) {
            return $this->reject('Invalid signature header.', 'signature_header_signature_invalid');
        }

        return null;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function headerLimit(string $key, int $defaultMin, int $defaultMax): array
    {
        $min = max(1, (int) config("device.signature.header_limits.{$key}.min", $defaultMin));
        $max = max($min, (int) config("device.signature.header_limits.{$key}.max", $defaultMax));

        return [$min, $max];
    }

    private function isBase64UrlToken(string $value, int $minLength, int $maxLength): bool
    {
        $length = strlen($value);
        if ($length < $minLength || $length > $maxLength) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9_-]+$/', $value) === 1;
    }

    private function dispatchSignatureActivityUpdate(int $deviceKeyId, int $deviceId): void
    {
        $debounceSeconds = max(
            1,
            (int) config('device.signature.activity_touch_debounce_seconds', 60),
        );

        $debounceCacheKey = DeviceSignatureCacheKey::activityTouch($deviceKeyId, $deviceId);

        if (!$this->signatureCache()->add($debounceCacheKey, 1, now()->addSeconds($debounceSeconds))) {
            return;
        }

        UpdateDeviceSignatureActivityJob::dispatch(
            $deviceKeyId,
            $deviceId,
            time(),
        );
    }

    private function verifySignature(string $signature, string $payload, string $publicKey): bool
    {
        if (config('device.signature.algorithm', 'ed25519') !== 'ed25519') {
            return false;
        }

        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            return false;
        }

        $decodedSignature = Base64Url::decode($signature);
        $decodedPublicKey = Base64Url::decode($publicKey);

        if ($decodedSignature === null || $decodedPublicKey === null) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($decodedSignature, $payload, $decodedPublicKey);
    }

    private function matchesAuthenticatedOwner(Request $request, string $ownerType, int $ownerId): bool
    {
        if (auth('member')->check()) {
            return $ownerType === DeviceOwnerType::MEMBER->value
                && $ownerId === (int) auth('member')->id();
        }

        if (auth('api')->check()) {
            return $ownerType === DeviceOwnerType::USER->value
                && $ownerId === (int) auth('api')->id();
        }

        return true;
    }

    private function reject(string $message, string $errorCode = 'invalid_signature'): JsonResponse
    {
        $this->logSignatureFailure($errorCode, $message);
        $this->collectSignatureFailureMetrics($errorCode);

        if ($this->registerSignatureFailureAttemptAndIsThrottled($errorCode)) {
            $request = request();
            $keyId = (string) $request->header($this->headerName('header_key_id'), '');

            return $this->rejectSignatureFailureThrottled($request, $keyId);
        }

        return response()->json(
            ApiError::create(
                status: 401,
                message: $message,
                errorCode: $errorCode,
            )->payload(),
            401,
        );
    }

    private function isSignatureFailureThrottled(Request $request, string $keyId): bool
    {
        if (!$this->isSignatureFailureThrottleEnabled() || $keyId === '') {
            return false;
        }

        return $this->signatureFailureLimiter()->tooManyAttempts(
            $this->signatureFailureThrottleKey($request, $keyId),
            $this->signatureFailureThrottleMaxAttempts(),
        );
    }

    private function registerSignatureFailureAttemptAndIsThrottled(string $errorCode): bool
    {
        if (!$this->shouldThrottleErrorCode($errorCode)) {
            return false;
        }

        $request = request();
        $keyId = (string) $request->header($this->headerName('header_key_id'), '');
        if ($keyId === '') {
            return false;
        }

        $throttleKey = $this->signatureFailureThrottleKey($request, $keyId);
        $this->signatureFailureLimiter()->hit($throttleKey, $this->signatureFailureThrottleDecaySeconds());

        return $this->signatureFailureLimiter()->tooManyAttempts($throttleKey, $this->signatureFailureThrottleMaxAttempts());
    }

    private function rejectSignatureFailureThrottled(Request $request, string $keyId): JsonResponse
    {
        $retryAfter = 1;
        if ($keyId !== '' && $this->isSignatureFailureThrottleEnabled()) {
            $retryAfter = max(1, $this->signatureFailureLimiter()->availableIn($this->signatureFailureThrottleKey($request, $keyId)));
        }

        $message = 'Too many invalid signature attempts.';
        $errorCode = 'signature_failure_throttled';

        $this->logSignatureFailure($errorCode, $message);
        $this->collectSignatureFailureMetrics($errorCode);

        return response()->json(
            ApiError::create(
                status: 429,
                message: $message,
                errorCode: $errorCode,
            )->payload(),
            429,
            ['Retry-After' => (string) $retryAfter],
        );
    }

    private function isSignatureFailureThrottleEnabled(): bool
    {
        return (bool) config('device.signature.failure_throttle_enabled', false);
    }

    private function signatureFailureThrottleMaxAttempts(): int
    {
        return max(1, (int) config('device.signature.failure_throttle_max_attempts', 20));
    }

    private function signatureFailureThrottleDecaySeconds(): int
    {
        return max(1, (int) config('device.signature.failure_throttle_decay_seconds', 60));
    }

    private function shouldThrottleErrorCode(string $errorCode): bool
    {
        if (!$this->isSignatureFailureThrottleEnabled()) {
            return false;
        }

        $configured = config(
            'device.signature.failure_throttle_error_codes',
            'signature_invalid,signature_nonce_reused',
        );

        if (is_string($configured)) {
            $errorCodes = array_filter(array_map('trim', explode(',', $configured)));
        } elseif (is_array($configured)) {
            $errorCodes = array_filter(array_map(
                static fn (mixed $value): string => is_string($value) ? trim($value) : '',
                $configured,
            ));
        } else {
            $errorCodes = ['signature_invalid', 'signature_nonce_reused'];
        }

        return in_array($errorCode, $errorCodes, true);
    }

    private function signatureFailureThrottleKey(Request $request, string $keyId): string
    {
        $ipHash = hash('sha256', (string) $request->ip());
        $keyHash = hash('sha256', $keyId);

        return sprintf('sig_fail_throttle:%s:%s', $ipHash, $keyHash);
    }

    private function logSignatureFailure(string $errorCode, string $message): void
    {
        if (!(bool) config('device.signature.failure_log_enabled', false)) {
            return;
        }

        $request = request();
        $keyHeader = $this->headerName('header_key_id');
        $timestampHeader = $this->headerName('header_timestamp');
        $nonceHeader = $this->headerName('header_nonce');
        $channel = (string) config('device.signature.failure_log_channel', '');

        $payload = [
            'error_code' => $errorCode,
            'message' => $message,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'member_id' => auth('member')->id(),
            'user_id' => auth('api')->id(),
            'key_id' => (string) $request->header($keyHeader, ''),
            'timestamp_header' => (string) $request->header($timestampHeader, ''),
            'nonce' => (string) $request->header($nonceHeader, ''),
        ];

        if ($channel !== '') {
            Log::channel($channel)->warning('device.signature.rejected', $payload);

            return;
        }

        Log::warning('device.signature.rejected', $payload);
    }

    private function collectSignatureFailureMetrics(string $errorCode): void
    {
        if (!(bool) config('device.signature.metrics_enabled', false)) {
            return;
        }

        $request = request();
        $bucket = gmdate('YmdHi');
        $namespace = (string) config('device.signature.metrics_namespace', 'device_signature');
        $ttl = max(60, (int) config('device.signature.metrics_ttl_seconds', 7200));
        $keyId = (string) $request->header($this->headerName('header_key_id'), '');
        $ipHash = hash('sha256', (string) $request->ip());

        $this->incrementMetric(sprintf('%s:reject:total:%s', $namespace, $bucket), $ttl);
        $this->incrementMetric(sprintf('%s:reject:error:%s:%s', $namespace, $errorCode, $bucket), $ttl);
        $this->incrementMetric(sprintf('%s:reject:ip:%s:%s', $namespace, $ipHash, $bucket), $ttl);

        if ($keyId !== '') {
            $keyHash = hash('sha256', $keyId);
            $this->incrementMetric(sprintf('%s:reject:key:%s:%s', $namespace, $keyHash, $bucket), $ttl);
        }
    }

    private function incrementMetric(string $key, int $ttl): void
    {
        $cache = $this->signatureMetricsCache();
        $cache->add($key, 0, now()->addSeconds($ttl));
        $cache->increment($key);
    }

    private function signatureCache(): CacheRepository
    {
        if ($this->signatureCacheRepository !== null) {
            return $this->signatureCacheRepository;
        }

        $store = (string) config('device.signature.cache_store', '');

        $this->signatureCacheRepository = $this->resolveCacheRepository($store);

        return $this->signatureCacheRepository;
    }

    private function signatureFailureThrottleCache(): CacheRepository
    {
        if ($this->signatureFailureThrottleCacheRepository !== null) {
            return $this->signatureFailureThrottleCacheRepository;
        }

        $store = (string) config('device.signature.failure_throttle_store', '');
        if ($store === '') {
            $store = (string) config('device.signature.cache_store', '');
        }

        $this->signatureFailureThrottleCacheRepository = $this->resolveCacheRepository($store);

        return $this->signatureFailureThrottleCacheRepository;
    }

    private function signatureMetricsCache(): CacheRepository
    {
        if ($this->signatureMetricsCacheRepository !== null) {
            return $this->signatureMetricsCacheRepository;
        }

        $store = (string) config('device.signature.metrics_store', '');
        if ($store === '') {
            $store = (string) config('device.signature.cache_store', '');
        }

        $this->signatureMetricsCacheRepository = $this->resolveCacheRepository($store);

        return $this->signatureMetricsCacheRepository;
    }

    private function signatureFailureLimiter(): CacheRateLimiter
    {
        if ($this->signatureFailureRateLimiter !== null) {
            return $this->signatureFailureRateLimiter;
        }

        $this->signatureFailureRateLimiter = new CacheRateLimiter($this->signatureFailureThrottleCache());

        return $this->signatureFailureRateLimiter;
    }

    private function resolveCacheRepository(string $store): CacheRepository
    {
        if ($store !== '') {
            return Cache::store($store);
        }

        return Cache::store();
    }
}
