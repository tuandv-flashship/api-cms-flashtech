<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListPostsRequest extends ParentRequest
{
    protected array $decode = [
        'category_ids.*',
        'tag_ids.*',
    ];
    
    
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'searchFields' => ['nullable', 'string', 'max:255'],
            'searchJoin' => ['nullable', Rule::in(['and', 'or'])],
            'orderBy' => ['nullable', Rule::in(['id', 'name', 'created_at', 'updated_at', 'views'])],
            'sortedBy' => ['nullable', Rule::in(['asc', 'desc'])],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'include' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('posts.index') ?? false;
    }
}
