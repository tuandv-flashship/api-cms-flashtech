<?php

namespace App\Containers\AppSection\Tools\Tests;

use App\Ship\Parents\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ContainerTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys(config('auth.guards')) as $guard) {
            Role::findOrCreate('admin', $guard);
        }

        $permissions = [
            'posts.import',
            'posts.export',
            'post-translations.import',
            'post-translations.export',
            'other-translations.import',
            'other-translations.export',
            'pages.import',
            'pages.export',
            'page-translations.import',
            'page-translations.export',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }
    }
}
