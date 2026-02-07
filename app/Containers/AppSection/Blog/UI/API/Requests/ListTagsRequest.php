<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListTagsRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'search' => ['nullable', 'string', 'max:255'],
            'order_by' => ['nullable', Rule::in(['id', 'name', 'created_at', 'updated_at'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('tags.index') ?? false;
    }
}
