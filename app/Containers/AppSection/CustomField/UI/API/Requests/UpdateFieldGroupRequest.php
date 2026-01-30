<?php

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UpdateFieldGroupRequest extends ParentRequest
{
    protected array $decode = [
        'field_group_id',
    ];

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
            'title' => ['sometimes', 'string', 'max:255'],
            'rules' => ['sometimes', 'array'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'group_items' => ['sometimes', 'array'],
            'deleted_items' => ['sometimes', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('custom-fields.edit') ?? false;
    }
}
