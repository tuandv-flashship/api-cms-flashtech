<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Requests;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Contracts\Validation\Validator;

final class CreateAdminMenuItemRequest extends ParentRequest
{
    protected array $decode = ['parent_id'];

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:admin_menu_items,id'],
            'key' => ['required', 'string', 'max:100', 'unique:admin_menu_items,key'],
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:120'],
            'route' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'children_display' => ['nullable', 'string', 'in:sidebar,panel'],
            'description' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('admin-menus.create') ?? false;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $parentId = $this->input('parent_id');
            if ($parentId === null) {
                return;
            }

            $maxDepth = (int) config('admin-menu-container.max_depth', 3);
            $depth = $this->getAncestorDepth((int) $parentId);

            // The new item will be at depth + 1.
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
