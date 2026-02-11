<?php

namespace App\Ship\Tests\Unit\Supports;

use App\Ship\Supports\AdminMenu;
use App\Ship\Tests\ShipTestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AdminMenu::class)]
final class AdminMenuTest extends ShipTestCase
{
    public function testParentNodeIsHiddenWhenNoChildrenAreAllowed(): void
    {
        config()->set('admin-menu', [
            [
                'id' => 'content',
                'name' => 'admin_menu.content',
                'permissions' => ['core.cms'],
                'children' => [
                    [
                        'id' => 'content.menus',
                        'name' => 'admin_menu.menus',
                        'route' => '/menus',
                        'permissions' => ['menus.index'],
                    ],
                ],
            ],
        ]);

        $user = new FakeAdminMenuUser(['core.cms']);

        $menu = app(AdminMenu::class)->forUser($user);

        $this->assertSame([], $menu);
    }

    public function testParentNodeIsReturnedWhenAnyChildIsAllowed(): void
    {
        config()->set('admin-menu', [
            [
                'id' => 'content',
                'name' => 'admin_menu.content',
                'permissions' => ['core.cms'],
                'children' => [
                    [
                        'id' => 'content.menus',
                        'name' => 'admin_menu.menus',
                        'route' => '/menus',
                        'permissions' => ['menus.index'],
                    ],
                ],
            ],
        ]);

        $user = new FakeAdminMenuUser(['menus.index']);

        $menu = app(AdminMenu::class)->forUser($user);

        $this->assertCount(1, $menu);
        $this->assertSame('content', $menu[0]['id']);
        $this->assertCount(1, $menu[0]['children']);
        $this->assertSame('content.menus', $menu[0]['children'][0]['id']);
    }

    public function testNodesAreSortedByPriority(): void
    {
        config()->set('admin-menu', [
            [
                'id' => 'b',
                'name' => 'admin_menu.blog',
                'priority' => 20,
            ],
            [
                'id' => 'a',
                'name' => 'admin_menu.pages',
                'priority' => 10,
            ],
        ]);

        $user = new FakeAdminMenuUser([]);

        $menu = app(AdminMenu::class)->forUser($user);

        $this->assertSame(['a', 'b'], array_column($menu, 'id'));
    }
}

final class FakeAdminMenuUser implements Authenticatable
{
    /**
     * @param array<int, string> $permissions
     */
    public function __construct(
        private readonly array $permissions,
    ) {
    }

    public function can(string $ability, array $arguments = []): bool
    {
        return in_array($ability, $this->permissions, true);
    }

    /**
     * @param array<int, string> $abilities
     */
    public function canAny($abilities, array $arguments = []): bool
    {
        foreach ($abilities as $ability) {
            if (in_array($ability, $this->permissions, true)) {
                return true;
            }
        }

        return false;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): int
    {
        return 1;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
