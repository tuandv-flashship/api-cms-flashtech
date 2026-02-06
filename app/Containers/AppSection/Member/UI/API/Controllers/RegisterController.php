<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\RegisterMemberAction;
use App\Containers\AppSection\Member\UI\API\Requests\RegisterMemberRequest;
use App\Containers\AppSection\Member\UI\API\Transformers\MemberTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class RegisterController extends ApiController
{
    public function __construct(
        private readonly RegisterMemberAction $registerMemberAction,
    ) {
    }

    public function registerMember(RegisterMemberRequest $request): JsonResponse
    {
        $member = $this->registerMemberAction->run($request);

        return Response::create($member, MemberTransformer::class)->created();
    }
}
