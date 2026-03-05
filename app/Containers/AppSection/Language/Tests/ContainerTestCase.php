<?php

namespace App\Containers\AppSection\Language\Tests;

use App\Containers\AppSection\Language\Models\Language;
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

        // Create language permissions
        $permissions = [
            'languages.index',
            'languages.create',
            'languages.edit',
            'languages.destroy',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        // Ensure at least one default language exists
        if (! Language::query()->where('lang_is_default', true)->exists()) {
            Language::factory()->vietnamese()->create();
        }
    }
}
