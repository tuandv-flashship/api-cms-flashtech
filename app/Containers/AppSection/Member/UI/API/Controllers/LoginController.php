<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use App\Containers\AppSection\Member\Actions\LoginMemberAction;
use App\Containers\AppSection\Member\UI\API\Requests\LoginMemberRequest;
use App\Containers\AppSection\Member\UI\API\Responders\MemberTokenResponder;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class LoginController extends ApiController
{
    public function loginMember(LoginMemberRequest $request): JsonResponse
    {
        try {
            $clientType = MemberClientType::fromRequest($request);

            $result = app(LoginMemberAction::class)->run($request, $clientType);
            $token = $result['token'];

            return app(MemberTokenResponder::class)->login(
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
            return response()->json([
                'message' => $exception->getMessage(),
                'error_code' => 'member_login_disabled',
            ], 403);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error_code' => 'member_not_found',
            ], 404);
        }

        if ($exception instanceof AuthenticationException) {
            $code = match ($exception->getMessage()) {
                'Invalid credentials.' => 'invalid_credentials',
                'Email is not verified.' => 'email_not_verified',
                'Member account is not active.' => 'member_not_active',
                default => 'authentication_failed',
            };

            return response()->json([
                'message' => $exception->getMessage(),
                'error_code' => $code,
            ], 401);
        }

        throw $exception;
    }
}
