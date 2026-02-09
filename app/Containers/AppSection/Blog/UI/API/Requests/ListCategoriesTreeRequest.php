<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListCategoriesTreeRequest extends ParentRequest
{
    protected array $decode = [];
    
    
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('categories.index') ?? false;
    }
}
