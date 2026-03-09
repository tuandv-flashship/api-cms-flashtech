<?php

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class CreateFieldGroupRequest extends ParentRequest
{
    protected array $decode = [];
    protected function prepareForValidation(): void
    {
        foreach (['rules', 'group_items', 'deleted_items'] as $key) {
            $value = $this->input($key);
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $this->merge([$key => $decoded]);
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'rules' => ['nullable', 'array'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'group_items' => ['nullable', 'array'],
            'group_items.*.title' => ['required_with:group_items', 'string', 'max:255'],
            'group_items.*.slug' => ['nullable', 'string', 'max:255'],
            'group_items.*.type' => ['required_with:group_items', 'string', Rule::in([
                'text', 'number', 'email', 'password', 'url', 'date', 'datetime',
                'time', 'color', 'textarea', 'checkbox', 'radio', 'select',
                'image', 'file', 'wysiwyg', 'repeater',
            ])],
            'group_items.*.instructions' => ['nullable', 'string', 'max:1000'],
            'group_items.*.options' => ['nullable'],
            'deleted_items' => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('custom-fields.create') ?? false;
    }
}
