<?php

namespace App\Containers\AppSection\Device\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class RotateUserDeviceKeyRequest extends ParentRequest
{
    protected array $decode = [];
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
            'key_id' => ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9_-]+=*$/'],
            'public_key' => ['required', 'string', 'max:500', 'regex:/^[A-Za-z0-9_-]+=*$/'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
