<?php

namespace App\Containers\AppSection\Translation\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class GetTranslationGroupRequest extends ParentRequest
{
    protected array $decode = [];
    protected function prepareForValidation(): void
    {
        $payload = [];
        $locale = $this->route('locale');

        if ($locale !== null) {
            $payload['locale'] = $locale;
        }

        $group = $this->route('group');
        if ($group !== null && $this->input('group') === null) {
            $payload['group'] = $group;
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
            'group' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\/-]+$/', 'not_regex:/\.\./'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('translations.index');
    }
}
