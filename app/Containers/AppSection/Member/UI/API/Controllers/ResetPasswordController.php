<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\ResetPasswordAction;
use App\Containers\AppSection\Member\UI\API\Requests\ResetPasswordRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ResetPasswordController extends ApiController
{
    public function __construct(
        private readonly ResetPasswordAction $resetPasswordAction,
    ) {
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->resetPasswordAction->run($request->validated());

        return Response::json([
            'message' => 'Password has been reset successfully.',
        ], 200);
    }
}
