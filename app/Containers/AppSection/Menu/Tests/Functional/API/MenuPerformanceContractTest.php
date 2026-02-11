<?php

namespace App\Containers\AppSection\Menu\Tests\Functional\API;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Menu\UI\API\Controllers\FindMenuByIdController;
use App\Containers\AppSection\Menu\UI\API\Controllers\GetMenuByLocationController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(FindMenuByIdController::class)]
#[CoversClass(GetMenuByLocationController::class)]
final class MenuPerformanceContractTest extends ApiTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->createOne();
        $permissions = Permission::query()->whereIn('name', [
            'menus.index',
            'menus.show',
            'menus.create',
            'menus.update',
            'menus.delete',
        ])->where('guard_name', 'api')->get();
        $this->user->syncPermissions($permissions);

        Cache::flush();
    }

    public function testFindMenuByIdMenuQueryCountIsStableRegardlessOfTreeSize(): void
    {
        $menu = Menu::query()->create(['name' => 'Perf', 'slug' => 'perf-menu', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'main-menu']);
        $this->seedRootNodes($menu->id, 2);

        $url = URL::action(FindMenuByIdController::class, ['id' => $menu->getHashedKey()]);

        $this->actingAs($this->user, 'api')->getJson($url)->assertOk();
        $smallQueries = $this->captureQueries(function () use ($url): void {
            $this->actingAs($this->user, 'api')->getJson($url)->assertOk();
        });

        $this->seedRootNodes($menu->id, 35, 100);

        $largeQueries = $this->captureQueries(function () use ($url): void {
            $this->actingAs($this->user, 'api')->getJson($url)->assertOk();
        });

        $smallCount = $this->countMenuQueries($smallQueries);
        $largeCount = $this->countMenuQueries($largeQueries);

        $this->assertGreaterThan(0, $smallCount);
        $this->assertSame($smallCount, $largeCount);
    }

    public function testPublicMenuEndpointSecondRequestUsesCacheWithoutMenuQueries(): void
    {
        $menu = Menu::query()->create(['name' => 'Footer', 'slug' => 'footer-perf', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'footer']);
        $this->seedRootNodes($menu->id, 5);

        $url = URL::action(GetMenuByLocationController::class, ['location' => 'footer']);

        $firstQueries = $this->captureQueries(function () use ($url): void {
            $this->getJson($url)->assertOk();
        });

        $secondQueries = $this->captureQueries(function () use ($url): void {
            $this->getJson($url)->assertOk();
        });

        $firstMenuQueries = $this->countMenuQueries($firstQueries);
        $secondMenuQueries = $this->countMenuQueries($secondQueries);

        $this->assertGreaterThan(0, $firstMenuQueries);
        $this->assertSame(0, $secondMenuQueries);
    }

    private function seedRootNodes(int $menuId, int $count, int $positionStart = 0): void
    {
        for ($i = 0; $i < $count; $i++) {
            MenuNode::query()->create([
                'menu_id' => $menuId,
                'title' => 'Node ' . ($positionStart + $i),
                'url' => '/node-' . ($positionStart + $i),
                'position' => $positionStart + $i,
            ]);
        }
    }
}
