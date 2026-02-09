<?php

namespace App\Containers\AppSection\LanguageAdvanced\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class UpdateSlugTranslationRequest extends ParentRequest
{
    protected array $decode = [
        'slug_id',
    ];
    protected function prepareForValidation(): void
    {
        $payload = [];

        $langCode = $this->input('lang_code');
        if ($langCode === null && $this->input('language') !== null) {
            $payload['lang_code'] = $this->input('language');
        }

        $key = $this->input('key');
        if ($key === null && $this->input('slug') !== null) {
            $payload['key'] = $this->input('slug');
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }

    public function rules(): array
    {
        return [
            'lang_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
            'key' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
