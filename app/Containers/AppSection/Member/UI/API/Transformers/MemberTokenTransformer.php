<?php

namespace App\Containers\AppSection\Member\UI\API\Transformers;

use App\Containers\AppSection\Authentication\Data\DTOs\PasswordToken;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class MemberTokenTransformer extends ParentTransformer
{
    public function transform(PasswordToken $token): array
    {
        $payload = self::payload($token);

        return [
            'type' => $token->getResourceKey(),
            ...$payload,
        ];
    }

    public static function payload(PasswordToken $token, string|null $clientType = null): array
    {
        $payload = [
            'token_type' => $token->tokenType,
            'access_token' => $token->accessToken,
            'expires_in' => $token->expiresIn,
        ];

        $resolvedClient = $clientType ?? MemberClientType::fromRequest(request());
        if (MemberClientType::isMobile($resolvedClient)) {
            $payload['refresh_token'] = $token->refreshToken->value();
        }

        return $payload;
    }
}
