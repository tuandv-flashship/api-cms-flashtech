<?php

namespace App\Containers\AppSection\Device\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

class RegisterUserDeviceRequest extends ParentRequest
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

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:191'],
            'key_id' => ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9_-]+=*$/'],
            'public_key' => ['required', 'string', 'max:500', 'regex:/^[A-Za-z0-9_-]+=*$/'],
            'platform' => ['sometimes', 'string', 'max:50'],
            'device_name' => ['sometimes', 'string', 'max:191'],
            'push_token' => ['sometimes', 'string', 'max:2048'],
            'push_provider' => ['sometimes', 'string', 'max:30', 'required_with:push_token'],
            'app_version' => ['sometimes', 'string', 'max:50'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
