<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

/**
 * No URL parameters to decode — this is a simple list endpoint.
 */
final class ListAdminMenuSectionsRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('admin-menus.index') ?? false;
    }
}
