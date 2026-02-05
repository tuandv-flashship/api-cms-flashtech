<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

class LoginMemberRequest extends ParentRequest
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

    protected function prepareForValidation(): void
    {
        if ($this->has('email') && !$this->has('login')) {
            $this->merge(['login' => $this->input('email')]);
        }

        if ($this->has('username') && !$this->has('login')) {
            $this->merge(['login' => $this->input('username')]);
        }
    }

    public function rules(): array
    {
        return [
            'login' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return (bool) config('member.auth.login_enabled', true);
    }
}
