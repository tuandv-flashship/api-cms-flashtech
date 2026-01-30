<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Supports\PostFormat;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UpdatePostRequest extends ParentRequest
{
    protected array $decode = [
        'post_id',
        'category_ids.*',
        'tag_ids.*',
    ];

    protected function prepareForValidation(): void
    {
        $gallery = $this->input('gallery');
        if (is_string($gallery)) {
            $decoded = json_decode($gallery, true);
            if (is_array($decoded)) {
                $this->merge(['gallery' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:400'],
            'content' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'image' => ['sometimes', 'nullable', 'string', 'max:255'],
            'format_type' => ['sometimes', 'nullable', 'string', 'max:30', Rule::in(array_keys(PostFormat::all()))],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'tag_names' => ['sometimes', 'array'],
            'tag_names.*' => ['string', 'max:120'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_meta' => ['sometimes', 'array'],
            'gallery' => ['sometimes', 'array'],
            'gallery.*.img' => ['required', 'string'],
            'gallery.*.description' => ['nullable', 'string'],
            'custom_fields' => ['sometimes'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('posts.edit') ?? false;
    }
}
