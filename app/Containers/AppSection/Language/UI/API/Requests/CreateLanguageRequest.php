<?php

namespace App\Containers\AppSection\Language\UI\API\Requests;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class CreateLanguageRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [
            'lang_name' => ['required', 'string', 'max:120'],
            'lang_locale' => ['required', 'string', 'max:20'],
            'lang_code' => ['required', 'string', 'max:20', 'unique:languages,lang_code'],
            'lang_flag' => ['nullable', 'string', 'max:20'],
            'lang_is_default' => ['sometimes', 'boolean'],
            'lang_is_rtl' => ['sometimes', 'boolean'],
            'lang_order' => ['sometimes', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('create', Language::class);
    }
}
