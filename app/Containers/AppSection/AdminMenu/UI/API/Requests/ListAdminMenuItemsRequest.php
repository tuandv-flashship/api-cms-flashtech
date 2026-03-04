<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class ListAdminMenuItemsRequest extends ParentRequest
{
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('admin-menus.index') ?? false;
    }
}
