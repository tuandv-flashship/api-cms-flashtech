<?php

namespace App\Containers\AppSection\Device\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

class UpdateMemberDeviceRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => '',
        'roles' => '',
    ];

    protected array $decode = [
        //
    ];

    protected array $urlParameters = [
        'device_id',
    ];

    protected function prepareForValidation(): void
    {
        $this->merge([
            'device_id' => $this->route('device_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:191'],
            'platform' => ['sometimes', 'nullable', 'string', 'max:50'],
            'device_name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'push_token' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'push_provider' => ['sometimes', 'nullable', 'string', 'max:30', 'required_with:push_token'],
            'app_version' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
