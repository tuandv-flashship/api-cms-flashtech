<?php

namespace App\Containers\AppSection\Menu\UI\API\Requests\Admin;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class UpdateMenuNodeTranslationRequest extends ParentRequest
{
    protected array $decode = [
        'id',
    ];

    protected function prepareForValidation(): void
    {
        $langCode = $this->input('lang_code') ?? $this->input('language');

        if ($langCode) {
            $normalized = LanguageAdvancedManager::normalizeLanguageCode((string) $langCode);
            if ($normalized !== null) {
                $this->merge(['lang_code' => $normalized]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'lang_code' => ['required', 'string', 'max:20', 'exists:languages,lang_code'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'url' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('menus.update') ?? false;
    }
}
