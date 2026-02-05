<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends ParentRequest
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
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
