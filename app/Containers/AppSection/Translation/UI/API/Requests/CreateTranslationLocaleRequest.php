<?php

namespace App\Containers\AppSection\Translation\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use App\Ship\Supports\Language;
use Illuminate\Validation\Rule;

final class CreateTranslationLocaleRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        $allowed = Language::getLocaleKeys();
        $aliases = array_map(static fn (string $locale): string => str_replace('_', '-', $locale), $allowed);

        return [
            'locale' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/', Rule::in(array_values(array_unique(array_merge($allowed, $aliases))))],
            'source' => ['sometimes', 'string', Rule::in(['github', 'copy'])],
            'include_vendor' => ['sometimes', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('translations.create');
    }
}
