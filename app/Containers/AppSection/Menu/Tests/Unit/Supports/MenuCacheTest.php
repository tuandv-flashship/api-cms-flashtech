<?php

namespace App\Containers\AppSection\Menu\Tests\Unit\Supports;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Containers\AppSection\Menu\Supports\MenuCache;
use App\Containers\AppSection\Menu\Tests\UnitTestCase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MenuCache::class)]
final class MenuCacheTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testRememberByLocationCachesCallbackResult(): void
    {
        $cache = app(MenuCache::class);
        $calls = 0;

        $result1 = $cache->rememberByLocation('main-menu', 'en', function () use (&$calls): array {
            $calls++;

            return ['value' => 'first'];
        });

        $result2 = $cache->rememberByLocation('main-menu', 'en', function () use (&$calls): array {
            $calls++;

            return ['value' => 'second'];
        });

        $this->assertSame('first', $result1['value']);
        $this->assertSame('first', $result2['value']);
        $this->assertSame(1, $calls);
    }

    public function testForgetByMenuIdClearsLocationKeys(): void
    {
        $menu = Menu::query()->create(['name' => 'Main', 'slug' => 'main-cache', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'main-menu']);

        $cache = app(MenuCache::class);
        $calls = 0;

        $first = $cache->rememberByLocation('main-menu', 'en', function () use (&$calls): string {
            $calls++;

            return 'cached-value-1';
        });
        $this->assertSame('cached-value-1', $first);
        $this->assertSame(1, $calls);

        $cache->forgetByMenuId($menu->id);

        $second = $cache->rememberByLocation('main-menu', 'en', function () use (&$calls): string {
            $calls++;

            return 'cached-value-2';
        });

        $this->assertSame('cached-value-2', $second);
        $this->assertSame(2, $calls);
    }
}
