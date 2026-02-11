<?php

namespace App\Containers\AppSection\Menu\UI\API\Requests\Admin;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class GetMenuOptionsRequest extends ParentRequest
{
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('menus.index') ?? false;
    }
}
