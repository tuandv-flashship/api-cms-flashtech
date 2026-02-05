<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\ForgotPasswordAction;
use App\Containers\AppSection\Member\UI\API\Requests\ForgotPasswordRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends ApiController
{
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        app(ForgotPasswordAction::class)->run($request->email);

        return Response::json([
            'message' => 'Password reset link sent.',
        ], 200);
    }
}
