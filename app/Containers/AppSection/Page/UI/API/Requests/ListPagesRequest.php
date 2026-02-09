<?php

namespace App\Containers\AppSection\Page\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Supports\PageOptions;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListPagesRequest extends ParentRequest
{
    protected array $decode = [];
    
    
    public function rules(): array
    {
        $templateKeys = PageOptions::templateKeys();

        return [
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'template' => array_merge(['nullable', 'string', 'max:60'], $templateKeys !== []
                ? [Rule::in($templateKeys)]
                : []),
            'search' => ['nullable', 'string', 'max:255'],
            'searchFields' => ['nullable', 'string', 'max:255'],
            'orderBy' => ['nullable', Rule::in(['id', 'name', 'created_at', 'updated_at'])],
            'sortedBy' => ['nullable', Rule::in(['asc', 'desc'])],
            'include' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.index') ?? false;
    }
}
