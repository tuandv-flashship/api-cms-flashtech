<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class DeleteCategoryRequest extends ParentRequest
{
    protected array $decode = [
        'category_id',
    ];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('categories.destroy') ?? false;
    }
}
