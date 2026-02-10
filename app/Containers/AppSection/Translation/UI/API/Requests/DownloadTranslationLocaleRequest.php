<?php

namespace App\Containers\AppSection\Translation\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class DownloadTranslationLocaleRequest extends ParentRequest
{
    protected array $decode = [];
    protected function prepareForValidation(): void
    {
        $locale = $this->route('locale');

        if ($locale !== null) {
            $this->merge([
                'locale' => $locale,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('translations.download');
    }
}
