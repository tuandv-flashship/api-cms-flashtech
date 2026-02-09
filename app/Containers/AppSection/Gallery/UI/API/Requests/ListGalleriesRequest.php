<?php

namespace App\Containers\AppSection\Gallery\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListGalleriesRequest extends ParentRequest
{
    protected array $decode = [
        'author_id',
    ];
    
    
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'is_featured' => ['nullable', 'boolean'],
            'author_id' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
            'order_by' => ['nullable', Rule::in(['id', 'name', 'order', 'created_at', 'updated_at'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'include' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('galleries.index') ?? false;
    }
}
