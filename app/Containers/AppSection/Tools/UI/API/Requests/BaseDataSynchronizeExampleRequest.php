<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

abstract class BaseDataSynchronizeExampleRequest extends ParentRequest
{
    protected string $permission = '';

    public function rules(): array
    {
        return [
            'format' => ['required', 'string', Rule::in(config('data-synchronize.formats', ['csv', 'xlsx']))],
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
