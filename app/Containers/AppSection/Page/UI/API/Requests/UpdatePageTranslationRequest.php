<?php

namespace App\Containers\AppSection\Page\UI\API\Requests;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class UpdatePageTranslationRequest extends ParentRequest
{
    protected array $decode = [
        'page_id',
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
    }

    public function rules(): array
    {
        return [
            'lang_code' => ['required', 'string', 'max:20', 'exists:languages,lang_code'],
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:400'],
            'content' => ['sometimes', 'nullable', 'string'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_meta' => ['sometimes', 'nullable', 'array'],
            'custom_fields' => ['sometimes'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.edit') ?? false;
    }
}
