<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class RestoreAdminMenuItemRequest extends ParentRequest
{
    protected array $decode = ['id'];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('admin-menus.update') ?? false;
    }
}
