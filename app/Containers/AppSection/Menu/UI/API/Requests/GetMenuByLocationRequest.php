<?php

namespace App\Containers\AppSection\Menu\UI\API\Requests;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class GetMenuByLocationRequest extends ParentRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'location' => $this->route('location'),
        ]);

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
            'location' => ['required', 'string', Rule::in(array_keys((array) config('menu.locations', [])))],
            'lang_code' => ['sometimes', 'string', 'max:20', 'exists:languages,lang_code'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
