<?php

namespace App\Containers\AppSection\Blog\Tests;

use App\Ship\Parents\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ContainerTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role for all guards (required by SuperAdminSeeder)
        foreach (array_keys(config('auth.guards')) as $guard) {
            Role::findOrCreate('admin', $guard);
        }

        // Create necessary permissions for tests
        $permissions = [
            // Posts
            'posts.index',
            'posts.create',
            'posts.edit',
            'posts.destroy',
            // Categories
            'categories.index',
            'categories.create',
            'categories.edit',
            'categories.destroy',
            // Tags
            'tags.index',
            'tags.create',
            'tags.edit',
            'tags.destroy',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }
    }
}
