<?php

namespace App\Containers\AppSection\Gallery\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class CreateGalleryRequest extends ParentRequest
{
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
            'name' => ['required', 'string', 'max:250'],
            'description' => ['required', 'string', 'max:10000'],
            'order' => ['nullable', 'integer', 'min:0', 'max:127'],
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'seo_meta' => ['nullable', 'array'],
            'gallery' => ['nullable', 'array'],
            'gallery.*.img' => ['required', 'string'],
            'gallery.*.description' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('galleries.create') ?? false;
    }
}
