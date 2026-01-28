<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UpdateTagRequest extends ParentRequest
{
    protected array $decode = [
        'tag_id',
    ];

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:400'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_meta' => ['sometimes', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('tags.edit') ?? false;
    }
}
