<?php

namespace App\Containers\AppSection\Gallery\UI\API\Requests;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class UpdateGalleryTranslationRequest extends ParentRequest
{
    protected array $decode = [
        'gallery_id',
    ];

    protected function prepareForValidation(): void
    {
        $langCode = $this->input('lang_code') ?? $this->input('language');

        if ($langCode) {
            $normalized = LanguageAdvancedManager::normalizeLanguageCode((string) $langCode);
            if ($normalized) {
                $this->merge(['lang_code' => $normalized]);
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
            'lang_code' => ['required', 'string', 'max:20', 'exists:languages,lang_code'],
            'name' => ['sometimes', 'string', 'max:250'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_meta' => ['sometimes', 'nullable', 'array'],
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
