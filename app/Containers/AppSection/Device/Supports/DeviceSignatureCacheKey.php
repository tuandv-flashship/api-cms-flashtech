<?php

namespace App\Containers\AppSection\Device\Supports;

final class DeviceSignatureCacheKey
{
    private static function prefix(): string
    {
        $prefix = (string) config('device.signature.cache_key_prefix', 'sig');

        return $prefix !== '' ? $prefix : 'sig';
    }

    public static function keyContext(string $keyId): string
    {
        return sprintf('%s_key_ctx:%s', self::prefix(), $keyId);
    }

    public static function nonce(int $deviceKeyId, string $nonce): string
    {
        return sprintf('%s_nonce:%d:%s', self::prefix(), $deviceKeyId, $nonce);
    }

    public static function activityTouch(int $deviceKeyId, int $deviceId): string
    {
        return sprintf('%s_activity_touch:%d:%d', self::prefix(), $deviceKeyId, $deviceId);
    }
}
