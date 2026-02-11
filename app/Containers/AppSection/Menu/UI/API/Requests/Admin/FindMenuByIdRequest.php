<?php

namespace App\Containers\AppSection\Menu\UI\API\Requests\Admin;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class FindMenuByIdRequest extends ParentRequest
{
    protected array $decode = [
        'id',
    ];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('menus.show') ?? false;
    }
}
