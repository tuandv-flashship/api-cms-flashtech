<?php

namespace App\Containers\AppSection\Device\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

class RevokeMemberDeviceKeyRequest extends ParentRequest
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
        'key_id',
    ];

    protected function prepareForValidation(): void
    {
        $this->merge([
            'device_id' => $this->route('device_id'),
            'key_id' => $this->route('key_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:191'],
            'key_id' => ['required', 'string', 'max:191'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
