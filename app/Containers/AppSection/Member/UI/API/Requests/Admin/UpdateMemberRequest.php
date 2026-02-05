<?php

namespace App\Containers\AppSection\Member\UI\API\Requests\Admin;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Values\MemberPhoneNormalizer;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => 'members.edit',
        'roles' => '',
    ];

    protected array $decode = [
        'id',
    ];

    protected array $urlParameters = [
        'id',
    ];

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = MemberPhoneNormalizer::normalize($this->input('phone'));
            $this->merge(['phone' => $phone]);
        }
    }

    public function rules(): array
    {
        $id = $this->id; // Decoded ID

        return [
            'name' => 'sometimes|string|max:191',
            'username' => [
                'sometimes',
                'nullable',
                'string',
                'max:191',
                'alpha_dash',
                Rule::unique('members', 'username')->ignore($id),
            ],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('members', 'email')->ignore($id),
            ],
            'password' => 'sometimes|string|min:8',
            'dob' => 'sometimes|date',
            'phone' => ['sometimes', 'string', 'regex:/^\\+[1-9]\\d{1,14}$/'],
            'description' => 'sometimes|string',
            'status' => ['sometimes', Rule::enum(MemberStatus::class)],
            'send_verification' => 'sometimes|boolean',
        ];
    }

    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('members.edit');
    }
}
