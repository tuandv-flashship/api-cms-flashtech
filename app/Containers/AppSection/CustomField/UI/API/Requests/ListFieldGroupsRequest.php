<?php

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListFieldGroupsRequest extends ParentRequest
{
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'order_by' => ['nullable', Rule::in(['id', 'title', 'order', 'created_at', 'updated_at'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('custom-fields.index') ?? false;
    }
}
