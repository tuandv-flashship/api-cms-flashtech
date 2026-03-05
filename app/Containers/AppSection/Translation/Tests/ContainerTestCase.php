<?php

namespace App\Containers\AppSection\Translation\Tests;

use App\Ship\Parents\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ContainerTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role for all guards
        foreach (array_keys(config('auth.guards')) as $guard) {
            Role::findOrCreate('admin', $guard);
        }

        // Create translation permissions
        $permissions = [
            'translations.index',
            'translations.create',
            'translations.edit',
            'translations.destroy',
            'translations.download',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }
    }
}
