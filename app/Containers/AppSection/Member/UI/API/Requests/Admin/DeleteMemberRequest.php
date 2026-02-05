<?php

namespace App\Containers\AppSection\Member\UI\API\Requests\Admin;

use App\Ship\Parents\Requests\Request as ParentRequest;

class DeleteMemberRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => 'members.destroy',
        'roles' => '',
    ];

    protected array $decode = [
        'id',
    ];

    protected array $urlParameters = [
        'id',
    ];

    public function rules(): array
    {
        return [
            //
        ];
    }

    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('members.destroy');
    }
}
