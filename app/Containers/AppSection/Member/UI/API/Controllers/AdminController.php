<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\DeleteMemberAction;
use App\Containers\AppSection\Member\Actions\CreateMemberAction;
use App\Containers\AppSection\Member\Actions\FindMemberByIdAction;
use App\Containers\AppSection\Member\Actions\GetAllMembersAction;
use App\Containers\AppSection\Member\Actions\UpdateMemberAction;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\CreateMemberRequest;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\DeleteMemberRequest;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\FindMemberByIdRequest;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\GetAllMembersRequest;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\UpdateMemberRequest;
use App\Containers\AppSection\Member\UI\API\Transformers\MemberTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class AdminController extends ApiController
{
    public function getAllMembers(GetAllMembersRequest $request): JsonResponse
    {
        $members = app(GetAllMembersAction::class)->run($request);

        return Response::create($members, MemberTransformer::class)->ok();
    }

    public function createMember(CreateMemberRequest $request): JsonResponse
    {
        $member = app(CreateMemberAction::class)->run($request);

        return Response::create($member, MemberTransformer::class)->created();
    }

    public function findMemberById(FindMemberByIdRequest $request): JsonResponse
    {
        $member = app(FindMemberByIdAction::class)->run($request);

        return Response::create($member, MemberTransformer::class)->ok();
    }

    public function updateMember(UpdateMemberRequest $request): JsonResponse
    {
        $member = app(UpdateMemberAction::class)->run($request);

        return Response::create($member, MemberTransformer::class)->ok();
    }

    public function deleteMember(DeleteMemberRequest $request): JsonResponse
    {
        app(DeleteMemberAction::class)->run($request);

        return Response::noContent();
    }
}
