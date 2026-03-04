<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class BulkSaveAdminMenuItemsRequest extends ParentRequest
{
    public function rules(): array
    {
        return [
            'remove_missing' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.key' => ['required', 'string', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.icon' => ['nullable', 'string', 'max:120'],
            'items.*.route' => ['nullable', 'string', 'max:255'],
            'items.*.permissions' => ['nullable', 'array'],
            'items.*.permissions.*' => ['string'],
            'items.*.children_display' => ['nullable', 'string', 'in:sidebar,panel'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.priority' => ['nullable', 'integer', 'min:0'],
            'items.*.is_active' => ['nullable', 'boolean'],
            'items.*.children' => ['sometimes', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('admin-menus.update') ?? false;
    }
}
