<?php

namespace App\Containers\AppSection\Page\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Supports\PageOptions;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class CreatePageRequest extends ParentRequest
{
    protected array $decode = [];
    
    
    public function rules(): array
    {
        $templateKeys = PageOptions::templateKeys();

        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:400'],
            'content' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'image' => ['nullable', 'string', 'max:255'],
            'template' => array_merge(['nullable', 'string', 'max:60'], $templateKeys !== []
                ? [Rule::in($templateKeys)]
                : []),
            'slug' => ['nullable', 'string', 'max:255'],
            'seo_meta' => ['nullable', 'array'],
            'custom_fields' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.create') ?? false;
    }
}
