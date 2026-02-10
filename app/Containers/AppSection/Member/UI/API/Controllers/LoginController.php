<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use App\Containers\AppSection\Member\Actions\LoginMemberAction;
use App\Containers\AppSection\Member\UI\API\Requests\LoginMemberRequest;
use App\Containers\AppSection\Member\UI\API\Responders\MemberTokenResponder;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Controllers\ApiController;
use App\Ship\Responders\ApiErrorResponder;
use App\Ship\Values\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

final class LoginController extends ApiController
{
    public function __construct(
        private readonly LoginMemberAction $loginMemberAction,
        private readonly MemberTokenResponder $memberTokenResponder,
        private readonly ApiErrorResponder $apiErrorResponder,
    ) {
    }

    public function loginMember(LoginMemberRequest $request): JsonResponse
    {
        try {
            $clientType = MemberClientType::fromRequest($request);

            $result = $this->loginMemberAction->run($request, $clientType);
            $token = $result['token'];

            return $this->memberTokenResponder->login(
                $result['member'],
                $token,
                $clientType,
            );
        } catch (Throwable $exception) {
            return $this->errorResponse($exception);
        }
    }

    private function errorResponse(Throwable $exception): JsonResponse
    {
        if ($exception instanceof AuthorizationException) {
            return $this->apiErrorResponder->respond(ApiError::create(
                status: 403,
                message: $exception->getMessage(),
                errorCode: 'member_login_disabled',
            ));
        }

        if ($exception instanceof AuthenticationException) {
            return $this->apiErrorResponder->respond(ApiError::create(
                status: 401,
                message: 'Invalid credentials.',
                errorCode: 'invalid_credentials',
            ));
        }

        throw $exception;
    }
}
