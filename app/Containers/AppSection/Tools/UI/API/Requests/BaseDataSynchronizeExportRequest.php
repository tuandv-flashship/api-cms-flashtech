<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

abstract class BaseDataSynchronizeExportRequest extends ParentRequest
{
    protected string $permission = '';

    public function rules(): array
    {
        return [
            'format' => ['sometimes', 'string', Rule::in(config('data-synchronize.formats', ['csv', 'xlsx']))],
            'columns' => ['sometimes', 'array'],
            'columns.*' => ['string'],
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
