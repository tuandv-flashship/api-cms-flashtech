<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\UI\API\Requests\GetMemberProfileRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Auth;

class GetMemberProfileAction extends ParentAction
{
    public function run(GetMemberProfileRequest $request): Member
    {
        return Auth::guard('member')->user();
    }
}
