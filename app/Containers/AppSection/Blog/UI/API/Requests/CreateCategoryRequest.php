<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class CreateCategoryRequest extends ParentRequest
{
    protected array $decode = [
        'parent_id',
    ];

    public function rules(): array
    {
        $parentRules = ['nullable', 'integer', 'min:0'];
        $parentId = $this->input('parent_id');
        if ($parentId !== null && (int) $parentId > 0) {
            $parentRules[] = 'exists:categories,id';
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:400'],
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'parent_id' => $parentRules,
            'icon' => ['nullable', 'string', 'max:60'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'slug' => ['nullable', 'string', 'max:255'],
            'seo_meta' => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('categories.create') ?? false;
    }
}
