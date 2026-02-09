<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use App\Containers\AppSection\Member\Values\MemberPhoneNormalizer;
use Illuminate\Validation\Rule;

final class RegisterMemberRequest extends ParentRequest
{
    protected array $decode = [];
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = MemberPhoneNormalizer::normalize($this->input('phone'));
            $this->merge(['phone' => $phone]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'username' => [
                'sometimes',
                'nullable',
                'string',
                'max:191',
                'alpha_dash',
                Rule::unique('members', 'username'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('members', 'email'),
            ],
            'password' => 'required|string|min:8|confirmed',
            'phone' => ['sometimes', 'string', 'regex:/^\\+[1-9]\\d{1,14}$/'],
        ];
    }

    public function authorize(): bool
    {
        return (bool) config('member.auth.registration_enabled', true);
    }
}
