<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends ParentRequest
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
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function authorize(): bool
    {
        return (bool) config('member.auth.login_enabled', true)
            && (bool) config('member.password_reset.enabled', true);
    }
}
