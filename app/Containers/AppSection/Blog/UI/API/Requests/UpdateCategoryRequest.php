<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UpdateCategoryRequest extends ParentRequest
{
    protected array $decode = [
        'category_id',
        'parent_id',
    ];

    public function rules(): array
    {
        $parentRules = ['sometimes', 'nullable', 'integer', 'min:0', Rule::notIn([$this->category_id])];
        $parentId = $this->input('parent_id');
        if ($parentId !== null && (int) $parentId > 0) {
            $parentRules[] = 'exists:categories,id';
        }

        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:400'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'parent_id' => $parentRules,
            'icon' => ['sometimes', 'nullable', 'string', 'max:60'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_meta' => ['sometimes', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('categories.edit') ?? false;
    }
}
