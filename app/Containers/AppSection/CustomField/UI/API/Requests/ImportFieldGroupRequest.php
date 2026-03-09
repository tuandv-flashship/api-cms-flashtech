<?php

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ImportFieldGroupRequest extends ParentRequest
{
    protected array $decode = [];

    protected function prepareForValidation(): void
    {
        // Support both JSON body and JSON string in 'data' field
        $data = $this->input('data');
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                $this->merge(['data' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.title' => ['required', 'string', 'max:255'],
            'data.status' => ['nullable', 'string'],
            'data.order' => ['nullable', 'integer', 'min:0'],
            'data.rules' => ['nullable', 'array'],
            'data.items' => ['nullable', 'array'],
            'data.items.*.title' => ['required_with:data.items', 'string', 'max:255'],
            'data.items.*.type' => ['required_with:data.items', 'string', Rule::in([
                'text', 'number', 'email', 'password', 'url', 'date', 'datetime',
                'time', 'color', 'textarea', 'checkbox', 'radio', 'select',
                'image', 'file', 'wysiwyg', 'repeater',
            ])],
            'data.items.*.slug' => ['nullable', 'string', 'max:255'],
            'data.items.*.instructions' => ['nullable', 'string', 'max:1000'],
            'data.items.*.options' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('custom-fields.create') ?? false;
    }
}
