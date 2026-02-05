<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\LogoutMemberAction;
use App\Containers\AppSection\Member\UI\API\Requests\LogoutMemberRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class LogoutController extends ApiController
{
    public function __invoke(LogoutMemberRequest $request, LogoutMemberAction $action): JsonResponse
    {
        $cookie = $action->run($request->user('member'));

        return Response::accepted([
            'message' => 'Token revoked successfully.',
        ])->withCookie($cookie);
    }
}
