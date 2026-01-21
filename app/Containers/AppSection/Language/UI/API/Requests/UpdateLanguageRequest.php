<?php

namespace App\Containers\AppSection\Language\UI\API\Requests;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UpdateLanguageRequest extends ParentRequest
{
    protected array $decode = [
        'language_id',
    ];

    public function rules(): array
    {
        return [
            'lang_name' => ['sometimes', 'string', 'max:120'],
            'lang_locale' => ['sometimes', 'string', 'max:20'],
            'lang_code' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('languages', 'lang_code')->ignore($this->language_id, 'lang_id'),
            ],
            'lang_flag' => ['nullable', 'string', 'max:20'],
            'lang_is_default' => ['sometimes', 'boolean'],
            'lang_is_rtl' => ['sometimes', 'boolean'],
            'lang_order' => ['sometimes', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', Language::class);
    }
}
