<?php

namespace App\Containers\AppSection\Member\UI\API\Requests\Admin;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Values\MemberPhoneNormalizer;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class CreateMemberRequest extends ParentRequest
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
            'dob' => 'sometimes|date',
            'phone' => ['sometimes', 'string', 'regex:/^\\+[1-9]\\d{1,14}$/'],
            'description' => 'sometimes|string',
            'avatar_id' => 'sometimes|string',
            'status' => ['sometimes', Rule::enum(MemberStatus::class)],
            'email_verified' => 'sometimes|boolean',
            'send_verification' => 'sometimes|boolean',
        ];
    }

    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('members.create');
    }
}
