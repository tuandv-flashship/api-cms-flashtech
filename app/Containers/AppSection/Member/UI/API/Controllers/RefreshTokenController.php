<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use App\Containers\AppSection\Member\Actions\RefreshMemberTokenAction;
use App\Containers\AppSection\Member\UI\API\Requests\RefreshMemberTokenRequest;
use App\Containers\AppSection\Member\UI\API\Responders\MemberTokenResponder;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Containers\AppSection\Member\Values\MemberRefreshToken;
use App\Containers\AppSection\Authentication\Values\RefreshToken;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie as CookieFacade;
use League\OAuth2\Server\Exception\OAuthServerException;

final class RefreshTokenController extends ApiController
{
    public function __invoke(RefreshMemberTokenRequest $request, RefreshMemberTokenAction $action): JsonResponse
    {
        try {
            $clientType = MemberClientType::fromRequest($request);

            $refreshToken = MemberRefreshToken::createFrom($request);

            $token = $action->run(
                RefreshToken::create($refreshToken->value()),
                $clientType,
            );

            return app(MemberTokenResponder::class)->refresh($token, $clientType);
        } catch (OAuthServerException $exception) {
            $payload = $exception->getPayload();
            $errorType = $exception->getErrorType();
            $status = $exception->getHttpStatusCode();

            $errorCode = match ($errorType) {
                'invalid_grant' => 'refresh_token_invalid',
                'invalid_client' => 'invalid_client',
                default => $errorType,
            };

            $response = response()->json([
                'message' => $payload['error_description'] ?? 'Unable to refresh token.',
                'error' => $payload['error'] ?? $errorType,
                'error_code' => $errorCode,
            ], $status);

            if ($errorType === 'invalid_grant') {
                $response->withCookie(CookieFacade::forget(MemberRefreshToken::cookieName()));
            }

            return $response;
        }
    }
}
