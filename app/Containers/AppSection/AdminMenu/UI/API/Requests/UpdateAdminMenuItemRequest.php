<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Requests;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Contracts\Validation\Validator;

final class UpdateAdminMenuItemRequest extends ParentRequest
{
    protected array $decode = ['id', 'parent_id'];

    public function rules(): array
    {
        $id = (int) $this->id;

        return [
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:admin_menu_items,id'],
            'key' => ['sometimes', 'string', 'max:100', 'unique:admin_menu_items,key,' . $id],
            'name' => ['sometimes', 'string', 'max:255'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:120'],
            'route' => ['sometimes', 'nullable', 'string', 'max:255'],
            'permissions' => ['sometimes', 'nullable', 'array'],
            'permissions.*' => ['string'],
            'children_display' => ['sometimes', 'nullable', 'string', 'in:sidebar,panel'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'priority' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('admin-menus.update') ?? false;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $parentId = $this->input('parent_id');
            if ($parentId === null || ! $this->has('parent_id')) {
                return;
            }

            // Prevent self-referencing.
            if ((int) $parentId === (int) $this->id) {
                $validator->errors()->add('parent_id', 'An item cannot be its own parent.');

                return;
            }

            $maxDepth = (int) config('admin-menu-container.max_depth', 3);
            $depth = $this->getAncestorDepth((int) $parentId);

            if (($depth + 1) > $maxDepth) {
                $validator->errors()->add(
                    'parent_id',
                    "Maximum menu depth of {$maxDepth} exceeded.",
                );
            }
        });
    }

    private function getAncestorDepth(int $parentId): int
    {
        $depth = 1;
        $current = AdminMenuItem::query()->find($parentId);

        while ($current !== null && $current->parent_id !== null) {
            $depth++;
            $current = AdminMenuItem::query()->find($current->parent_id);
        }

        return $depth;
    }
}
