<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use App\Containers\AppSection\Member\Actions\RefreshMemberTokenAction;
use App\Containers\AppSection\Member\UI\API\Requests\RefreshMemberTokenRequest;
use App\Containers\AppSection\Member\UI\API\Responders\MemberTokenResponder;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Containers\AppSection\Member\Values\MemberRefreshToken;
use App\Containers\AppSection\Authentication\Values\RefreshToken;
use App\Ship\Parents\Controllers\ApiController;
use App\Ship\Responders\ApiErrorResponder;
use App\Ship\Values\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie as CookieFacade;
use League\OAuth2\Server\Exception\OAuthServerException;

final class RefreshTokenController extends ApiController
{
    public function __construct(
        private readonly RefreshMemberTokenAction $refreshMemberTokenAction,
        private readonly MemberTokenResponder $memberTokenResponder,
        private readonly ApiErrorResponder $apiErrorResponder,
    ) {
    }

    public function __invoke(RefreshMemberTokenRequest $request): JsonResponse
    {
        try {
            $clientType = MemberClientType::fromRequest($request);

            $refreshToken = MemberRefreshToken::createFrom($request);

            $token = $this->refreshMemberTokenAction->run(
                RefreshToken::create($refreshToken->value()),
                $clientType,
            );

            return $this->memberTokenResponder->refresh($token, $clientType);
        } catch (OAuthServerException $exception) {
            $payload = $exception->getPayload();
            $errorType = $exception->getErrorType();
            $status = $exception->getHttpStatusCode();

            $errorCode = match ($errorType) {
                'invalid_grant' => 'refresh_token_invalid',
                'invalid_client' => 'invalid_client',
                default => $errorType,
            };

            $response = $this->apiErrorResponder->respond(ApiError::create(
                status: $status,
                message: $payload['error_description'] ?? 'Unable to refresh token.',
                errorCode: $errorCode,
                extra: [
                    'error' => $payload['error'] ?? $errorType,
                ],
            ));

            if ($errorType === 'invalid_grant') {
                $response->withCookie(CookieFacade::forget(MemberRefreshToken::cookieName()));
            }

            return $response;
        }
    }
}
