<?php

namespace App\Containers\AppSection\Device\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

class RevokeUserDeviceRequest extends ParentRequest
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
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
