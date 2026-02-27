<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Supports\PostFormat;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class CreatePostRequest extends ParentRequest
{
    protected array $decode = [
        'category_ids.*',
        'tag_ids.*',
    ];
    protected function prepareForValidation(): void
    {
        // Clean up array fields: filter out empty/null values so FE can send [""] to clear
        foreach (['category_ids', 'tag_ids', 'tag_names'] as $field) {
            if ($this->has($field) && is_array($this->input($field))) {
                $this->merge([
                    $field => array_values(array_filter($this->input($field), static fn ($v) => $v !== null && $v !== '')),
                ]);
            }
        }

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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:400'],
            'content' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'string', 'max:255'],
            'banner_image' => ['nullable', 'string', 'max:255'],
            'format_type' => ['nullable', 'string', 'max:30', Rule::in(array_keys(PostFormat::all()))],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'tag_names' => ['nullable', 'array'],
            'tag_names.*' => ['string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:255'],
            'seo_meta' => ['nullable', 'array'],
            'gallery' => ['nullable', 'array'],
            'gallery.*.img' => ['required', 'string'],
            'gallery.*.description' => ['nullable', 'string'],
            'custom_fields' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('posts.create') ?? false;
    }
}
