<?php

namespace App\Containers\AppSection\Page\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Supports\PageOptions;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UpdatePageRequest extends ParentRequest
{
    protected array $decode = [
        'page_id',
    ];

    public function rules(): array
    {
        $templateKeys = PageOptions::templateKeys();

        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:400'],
            'content' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'image' => ['sometimes', 'nullable', 'string', 'max:255'],
            'template' => array_merge(['sometimes', 'nullable', 'string', 'max:60'], $templateKeys !== []
                ? [Rule::in($templateKeys)]
                : []),
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_meta' => ['sometimes', 'array'],
            'custom_fields' => ['sometimes'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.edit') ?? false;
    }
}
