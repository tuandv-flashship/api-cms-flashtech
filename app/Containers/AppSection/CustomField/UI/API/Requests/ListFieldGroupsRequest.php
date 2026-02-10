<?php

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListFieldGroupsRequest extends ParentRequest
{
    protected array $decode = [];
    
    
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'searchFields' => ['nullable', 'string', 'max:255'],
            'orderBy' => ['nullable', Rule::in(['id', 'title', 'order', 'created_at', 'updated_at'])],
            'sortedBy' => ['nullable', Rule::in(['asc', 'desc'])],
            'include' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('custom-fields.index') ?? false;
    }
}
