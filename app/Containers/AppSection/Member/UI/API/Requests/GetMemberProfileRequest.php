<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

class GetMemberProfileRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => '',
        'roles' => '',
    ];

    protected array $decode = [
        //
    ];

    protected array $urlParameters = [
        //
    ];

    public function rules(): array
    {
        return [
            //
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
