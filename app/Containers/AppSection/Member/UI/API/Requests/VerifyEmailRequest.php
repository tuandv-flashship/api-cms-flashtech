<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

class VerifyEmailRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => '',
        'roles' => '',
    ];

    protected array $decode = [
        'id',
    ];

    protected array $urlParameters = [
        'id',
        'hash',
    ];

    public function rules(): array
    {
        return [
            //
        ];
    }

    public function authorize(): bool
    {
        return (bool) config('member.email_verification.enabled');
    }
}
