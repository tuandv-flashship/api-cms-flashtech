<?php

namespace App\Containers\AppSection\Page\Tests\Functional;

use App\Ship\Parents\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ApiTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role for all guards
        foreach (array_keys(config('auth.guards')) as $guard) {
            if ($guard === 'sanctum') continue; // sanctum is not a guard provider in the same way, usually web/api
            try {
                Role::findOrCreate('admin', $guard);
            } catch (\Throwable) {}
        }

        // Create permissions
        $permissions = [
            'pages.index',
            'pages.create',
            'pages.edit',
            'pages.destroy',
            'pages.show', // Maybe used
        ];

        foreach ($permissions as $permission) {
            try {
                Permission::findOrCreate($permission, 'api');
            } catch (\Throwable) {}
        }
    }
}
