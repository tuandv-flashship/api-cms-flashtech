<?php

namespace App\Containers\AppSection\Member\UI\API\Requests;

use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Requests\Request as ParentRequest;
use App\Containers\AppSection\Member\Values\MemberPhoneNormalizer;
use Illuminate\Validation\Rule;

final class UpdateMemberProfileRequest extends ParentRequest
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
        $memberId = $this->user('member')?->id;

        return [
            'name' => 'sometimes|string|max:191',
            'username' => [
                'sometimes',
                'nullable',
                'string',
                'max:191',
                'alpha_dash',
                Rule::unique('members', 'username')->ignore($memberId),
            ],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('members', 'email')->ignore($memberId),
            ],
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|string|min:8|confirmed',
            'dob' => 'sometimes|date',
            'phone' => ['sometimes', 'string', 'regex:/^\\+[1-9]\\d{1,14}$/'],
            'description' => 'sometimes|string',
            'avatar_id' => 'sometimes|string', // Assuming hashed ID
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
