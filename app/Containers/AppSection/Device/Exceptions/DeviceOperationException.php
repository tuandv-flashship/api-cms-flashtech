<?php

namespace App\Containers\AppSection\Device\Exceptions;

use App\Ship\Exceptions\ApiErrorException;

final class DeviceOperationException extends ApiErrorException
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        string $message,
        string $errorCode,
        int $status,
        array $extra = [],
    ) {
        parent::__construct($message, $errorCode, $status, $extra);
    }

    public static function invalidPublicKey(): self
    {
        return new self(
            message: 'The public_key is invalid.',
            errorCode: 'invalid_public_key',
            status: 422,
            extra: ['errors' => ['public_key' => ['The public_key is invalid.']]],
        );
    }

    public static function invalidPublicKeyLength(): self
    {
        return new self(
            message: 'The public_key length is invalid.',
            errorCode: 'invalid_public_key_length',
            status: 422,
            extra: ['errors' => ['public_key' => ['The public_key length is invalid.']]],
        );
    }

    public static function keyIdTaken(): self
    {
        return new self(
            message: 'The key_id has already been taken.',
            errorCode: 'key_id_taken',
            status: 422,
            extra: ['errors' => ['key_id' => ['The key_id has already been taken.']]],
        );
    }

    public static function deviceNotFound(): self
    {
        return new self(
            message: 'Device not found.',
            errorCode: 'device_not_found',
            status: 404,
        );
    }

    public static function deviceKeyNotFound(): self
    {
        return new self(
            message: 'Device key not found.',
            errorCode: 'device_key_not_found',
            status: 404,
        );
    }
}
