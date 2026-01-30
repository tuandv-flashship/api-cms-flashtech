<?php

namespace App\Containers\AppSection\Page\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Supports\PageOptions;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListPagesRequest extends ParentRequest
{
    public function rules(): array
    {
        $templateKeys = PageOptions::templateKeys();

        return [
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'template' => array_merge(['nullable', 'string', 'max:60'], $templateKeys !== []
                ? [Rule::in($templateKeys)]
                : []),
            'search' => ['nullable', 'string', 'max:255'],
            'order_by' => ['nullable', Rule::in(['id', 'name', 'created_at', 'updated_at'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.index') ?? false;
    }
}
