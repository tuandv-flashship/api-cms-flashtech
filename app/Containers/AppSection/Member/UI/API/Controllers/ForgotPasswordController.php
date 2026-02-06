<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\ForgotPasswordAction;
use App\Containers\AppSection\Member\UI\API\Requests\ForgotPasswordRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ForgotPasswordController extends ApiController
{
    public function __construct(
        private readonly ForgotPasswordAction $forgotPasswordAction,
    ) {
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->forgotPasswordAction->run($request->email);

        return Response::json([
            'message' => 'Password reset link sent.',
        ], 200);
    }
}
