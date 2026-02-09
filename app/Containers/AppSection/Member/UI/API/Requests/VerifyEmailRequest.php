<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class VerifyEmailRequest extends ParentRequest
{
    protected array $decode = [
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
        return (bool) config('member.email_verification.enabled');
    }
}
