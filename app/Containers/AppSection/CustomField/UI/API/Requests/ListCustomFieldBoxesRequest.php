<?php

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Containers\AppSection\CustomField\Supports\CustomFieldOptions;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListCustomFieldBoxesRequest extends ParentRequest
{
    protected array $decode = [
        'reference_id',
    ];

    protected function prepareForValidation(): void
    {
        $rules = $this->input('rules');
        if (is_string($rules)) {
            $decoded = json_decode($rules, true);
            if (is_array($decoded)) {
                $this->merge(['rules' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'model' => ['required', 'string', Rule::in(array_keys(CustomFieldOptions::supportedModules()))],
            'reference_id' => ['nullable', 'integer', 'min:1'],
            'rules' => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('custom-fields.index') ?? false;
    }
}
