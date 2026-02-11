<?php

namespace App\Containers\AppSection\Menu\UI\API\Requests\Admin;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class ListMenusRequest extends ParentRequest
{
    public function rules(): array
    {
        return [
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:120'],
            'status' => ['sometimes', 'string', 'max:30'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('menus.index') ?? false;
    }
}
