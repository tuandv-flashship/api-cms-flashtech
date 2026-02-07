<?php

namespace App\Containers\AppSection\Device\Supports;

use App\Containers\AppSection\Device\Exceptions\DeviceOperationException;
use App\Ship\Supports\Base64Url;

final class PublicKeyValidator
{
    public static function assertValidEd25519PublicKey(string $publicKey): void
    {
        $decoded = Base64Url::decode($publicKey);

        if ($decoded === null) {
            throw DeviceOperationException::invalidPublicKey();
        }

        if (defined('SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES')) {
            $expected = SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES;
            if (strlen($decoded) !== $expected) {
                throw DeviceOperationException::invalidPublicKeyLength();
            }
        }
    }
}

