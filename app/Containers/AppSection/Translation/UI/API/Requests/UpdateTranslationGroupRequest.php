<?php

namespace App\Containers\AppSection\Translation\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class UpdateTranslationGroupRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
            'group' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\/-]+$/', 'not_regex:/\.\./'],
            'translations' => ['required', 'array'],
            'translations.*' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('translations.edit');
    }
}
