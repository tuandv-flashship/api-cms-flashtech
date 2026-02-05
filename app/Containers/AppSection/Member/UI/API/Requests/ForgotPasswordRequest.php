<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

class ForgotPasswordRequest extends ParentRequest
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
            'email' => 'required|email',
        ];
    }

    public function authorize(): bool
    {
        return (bool) config('member.auth.login_enabled', true)
            && (bool) config('member.password_reset.enabled', true);
    }
}
