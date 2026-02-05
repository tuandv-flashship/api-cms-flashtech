<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\RegisterMemberAction;
use App\Containers\AppSection\Member\UI\API\Requests\RegisterMemberRequest;
use App\Containers\AppSection\Member\UI\API\Transformers\MemberTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class RegisterController extends ApiController
{
    public function registerMember(RegisterMemberRequest $request): JsonResponse
    {
        $member = app(RegisterMemberAction::class)->run($request);

        return Response::create($member, MemberTransformer::class)->created();
    }
}
