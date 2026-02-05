<?php

namespace App\Containers\AppSection\Member\UI\API\Requests\Admin;

use App\Ship\Parents\Requests\Request as ParentRequest;

class GetAllMembersRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => 'members.index',
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
        return $this->user() && $this->user()->can('members.index');
    }
}
