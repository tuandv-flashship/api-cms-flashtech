<?php

namespace App\Containers\AppSection\AdminMenu\Data\Repositories;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

final class AdminMenuItemRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'key' => '=',
        'name' => 'like',
        'is_active' => '=',
    ];

    public function model(): string
    {
        return AdminMenuItem::class;
    }
}
