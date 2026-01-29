<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

abstract class BaseDataSynchronizeImportRequest extends ParentRequest
{
    protected string $permission = '';

    public function rules(): array
    {
        return [
            'file_name' => ['required', 'string'],
            'offset' => ['required', 'integer', 'min:0'],
            'limit' => ['required', 'integer', 'min:1'],
            'total' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        if ($this->permission === '') {
            return false;
        }

        return $this->user()?->can($this->permission) ?? false;
    }
}
