<?php

namespace App\Containers\AppSection\Gallery\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UpdateGalleryRequest extends ParentRequest
{
    protected array $decode = [
        'gallery_id',
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
            'name' => ['sometimes', 'string', 'max:250'],
            'description' => ['sometimes', 'string', 'max:10000'],
            'order' => ['sometimes', 'integer', 'min:0', 'max:127'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'image' => ['sometimes', 'nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_meta' => ['sometimes', 'array'],
            'gallery' => ['sometimes', 'array'],
            'gallery.*.img' => ['required', 'string'],
            'gallery.*.description' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('galleries.edit') ?? false;
    }
}
