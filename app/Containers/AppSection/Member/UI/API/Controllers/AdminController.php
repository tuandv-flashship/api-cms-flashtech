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

final class AdminController extends ApiController
{
    public function __construct(
        private readonly GetAllMembersAction $getAllMembersAction,
        private readonly CreateMemberAction $createMemberAction,
        private readonly FindMemberByIdAction $findMemberByIdAction,
        private readonly UpdateMemberAction $updateMemberAction,
        private readonly DeleteMemberAction $deleteMemberAction,
    ) {
    }

    public function getAllMembers(GetAllMembersRequest $request): JsonResponse
    {
        $members = $this->getAllMembersAction->run($request);

        return Response::create($members, MemberTransformer::class)->ok();
    }

    public function createMember(CreateMemberRequest $request): JsonResponse
    {
        $member = $this->createMemberAction->run($request);

        return Response::create($member, MemberTransformer::class)->created();
    }

    public function findMemberById(FindMemberByIdRequest $request): JsonResponse
    {
        $member = $this->findMemberByIdAction->run($request);

        return Response::create($member, MemberTransformer::class)->ok();
    }

    public function updateMember(UpdateMemberRequest $request): JsonResponse
    {
        $member = $this->updateMemberAction->run($request);

        return Response::create($member, MemberTransformer::class)->ok();
    }

    public function deleteMember(DeleteMemberRequest $request): JsonResponse
    {
        $this->deleteMemberAction->run($request);

        return Response::noContent();
    }
}
