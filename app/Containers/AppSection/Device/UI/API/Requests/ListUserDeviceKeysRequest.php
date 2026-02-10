<?php

namespace App\Containers\AppSection\Device\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class ListUserDeviceKeysRequest extends ParentRequest
{
    protected array $decode = [];
    
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:191'],
            'limit' => ['sometimes', 'integer', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'include_public_key' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'device_id' => $this->route('device_id'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }
}
