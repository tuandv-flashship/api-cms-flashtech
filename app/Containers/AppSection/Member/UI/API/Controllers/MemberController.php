<?php

namespace App\Containers\AppSection\Member\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Member\Actions\ChangePasswordAction;
use App\Containers\AppSection\Member\Actions\GetMemberProfileAction;
use App\Containers\AppSection\Member\Actions\UpdateMemberProfileAction;
use App\Containers\AppSection\Member\Actions\VerifyEmailAction;
use App\Containers\AppSection\Member\UI\API\Requests\ChangePasswordRequest;
use App\Containers\AppSection\Member\UI\API\Requests\GetMemberProfileRequest;
use App\Containers\AppSection\Member\UI\API\Requests\UpdateMemberProfileRequest;
use App\Containers\AppSection\Member\UI\API\Requests\VerifyEmailRequest;
use App\Containers\AppSection\Member\UI\API\Transformers\MemberTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class MemberController extends ApiController
{
    public function __construct(
        private readonly GetMemberProfileAction $getMemberProfileAction,
        private readonly UpdateMemberProfileAction $updateMemberProfileAction,
        private readonly VerifyEmailAction $verifyEmailAction,
        private readonly ChangePasswordAction $changePasswordAction,
    ) {
    }

    public function getProfile(GetMemberProfileRequest $request): JsonResponse
    {
        $member = $this->getMemberProfileAction->run($request);

        return Response::create($member, MemberTransformer::class)->ok();
    }

    public function updateProfile(UpdateMemberProfileRequest $request): JsonResponse
    {
        $transporter = \App\Containers\AppSection\Member\UI\API\Transporters\UpdateMemberProfileTransporter::fromRequest($request);
        $member = $this->updateMemberProfileAction->run($transporter);

        return Response::create($member, MemberTransformer::class)->ok();
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $this->verifyEmailAction->run($request);

        return Response::ok(['message' => 'Email verified successfully.']);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->changePasswordAction->run($request);

        return Response::noContent();
    }
}
