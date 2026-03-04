<?php

namespace App\Containers\AppSection\AdminMenu\Tests;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Models\AdminMenuItemTranslation;
use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ContainerTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys(config('auth.guards')) as $guard) {
            if ($guard === 'sanctum') {
                continue;
            }

            Role::findOrCreate('admin', $guard);
        }

        $permissions = [
            'admin-menus.index',
            'admin-menus.show',
            'admin-menus.create',
            'admin-menus.update',
            'admin-menus.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        Language::query()->firstOrCreate(
            ['lang_code' => 'en'],
            [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_flag' => 'us',
                'lang_is_default' => true,
                'lang_order' => 1,
                'lang_is_rtl' => false,
            ],
        );

        Language::query()->firstOrCreate(
            ['lang_code' => 'vi'],
            [
                'lang_name' => 'Vietnamese',
                'lang_locale' => 'vi',
                'lang_flag' => 'vn',
                'lang_is_default' => false,
                'lang_order' => 2,
                'lang_is_rtl' => false,
            ],
        );

        // Keep tests isolated.
        AdminMenuItemTranslation::query()->delete();
        AdminMenuItem::query()->forceDelete();
    }
}
