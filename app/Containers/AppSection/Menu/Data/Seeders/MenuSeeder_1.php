<?php

namespace App\Containers\AppSection\Menu\Data\Seeders;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Supports\MenuNodeResolver;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Slug\Supports\SlugRuntimeServices;
use App\Ship\Parents\Seeders\Seeder as ParentSeeder;

final class MenuSeeder_1 extends ParentSeeder
{
    public function run(): void
    {
        $mainMenu = Menu::query()->updateOrCreate(
            ['slug' => 'main-menu'],
            [
                'name' => 'Main Menu',
                'status' => 'published',
            ],
        );

        $footerMenu = Menu::query()->updateOrCreate(
            ['slug' => 'footer-menu'],
            [
                'name' => 'Footer Menu',
                'status' => 'published',
            ],
        );

        MenuLocation::query()->updateOrCreate(
            ['location' => 'main-menu'],
            ['menu_id' => (int) $mainMenu->getKey()],
        );

        MenuLocation::query()->updateOrCreate(
            ['location' => 'footer'],
            ['menu_id' => (int) $footerMenu->getKey()],
        );

        $homeNode = MenuNode::query()->updateOrCreate(
            [
                'menu_id' => (int) $mainMenu->getKey(),
                'parent_id' => null,
                'position' => 0,
            ],
            [
                'title' => 'Home',
                'url' => '/',
                'url_source' => 'custom',
                'title_source' => 'custom',
                'target' => '_self',
                'has_child' => false,
            ],
        );

        MenuNode::query()->updateOrCreate(
            [
                'menu_id' => (int) $mainMenu->getKey(),
                'parent_id' => null,
                'position' => 1,
            ],
            [
                'title' => 'Blog',
                'url' => '/blog',
                'url_source' => 'custom',
                'title_source' => 'custom',
                'target' => '_self',
                'has_child' => false,
            ],
        );

        $samplePage = Page::query()->firstOrCreate(
            ['name' => 'Menu Seeder Sample Page'],
            [
                'content' => '<p>Sample page for menu reference node.</p>',
                'description' => 'Sample page for menu reference node.',
                'status' => 'published',
                'user_id' => null,
            ],
        );

        SlugRuntimeServices::helper()->createSlug($samplePage);

        $resolvedReference = app(MenuNodeResolver::class)->resolve(Page::class, (int) $samplePage->getKey());

        MenuNode::query()->updateOrCreate(
            [
                'menu_id' => (int) $mainMenu->getKey(),
                'parent_id' => null,
                'position' => 2,
            ],
            [
                'reference_type' => $resolvedReference['reference_type'],
                'reference_id' => (int) $samplePage->getKey(),
                'title' => $resolvedReference['title'] ?? 'Menu Seeder Sample Page',
                'url' => $resolvedReference['url'] ?? '/menu-seeder-sample-page',
                'url_source' => 'resolved',
                'title_source' => 'resolved',
                'target' => '_self',
                'has_child' => false,
            ],
        );

        MenuNode::query()->updateOrCreate(
            [
                'menu_id' => (int) $footerMenu->getKey(),
                'parent_id' => null,
                'position' => 0,
            ],
            [
                'title' => 'Contact',
                'url' => '/contact',
                'url_source' => 'custom',
                'title_source' => 'custom',
                'target' => '_self',
                'has_child' => false,
            ],
        );

        MenuNode::query()->whereKey((int) $homeNode->getKey())->update(['has_child' => false]);
    }
}
