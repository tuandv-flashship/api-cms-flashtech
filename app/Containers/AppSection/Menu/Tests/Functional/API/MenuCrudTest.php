<?php

namespace App\Containers\AppSection\Menu\Tests\Functional\API;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Models\MenuNodeTranslation;
use App\Containers\AppSection\Menu\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Menu\UI\API\Controllers\CreateMenuController;
use App\Containers\AppSection\Menu\UI\API\Controllers\DeleteMenuController;
use App\Containers\AppSection\Menu\UI\API\Controllers\FindMenuByIdController;
use App\Containers\AppSection\Menu\UI\API\Controllers\GetMenuOptionsController;
use App\Containers\AppSection\Menu\UI\API\Controllers\ListMenusController;
use App\Containers\AppSection\Menu\UI\API\Controllers\UpdateMenuController;
use App\Containers\AppSection\Menu\UI\API\Controllers\UpdateMenuNodeTranslationController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(ListMenusController::class)]
#[CoversClass(CreateMenuController::class)]
#[CoversClass(FindMenuByIdController::class)]
#[CoversClass(UpdateMenuController::class)]
#[CoversClass(DeleteMenuController::class)]
#[CoversClass(GetMenuOptionsController::class)]
#[CoversClass(UpdateMenuNodeTranslationController::class)]
final class MenuCrudTest extends ApiTestCase
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
    }

    public function testListMenus(): void
    {
        Menu::query()->create(['name' => 'Main', 'slug' => 'main', 'status' => 'published']);
        Menu::query()->create(['name' => 'Footer', 'slug' => 'footer', 'status' => 'published']);

        $response = $this->actingAs($this->user, 'api')->getJson(URL::action(ListMenusController::class));

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->has('data')
            ->etc());
    }

    public function testCreateMenu(): void
    {
        $payload = [
            'name' => 'Main Menu',
            'slug' => 'main-menu-crud',
            'status' => 'published',
            'locations' => ['main-menu'],
            'nodes' => [
                [
                    'title' => 'Home',
                    'url' => '/',
                    'target' => '_self',
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')->postJson(URL::action(CreateMenuController::class), $payload);

        $response->assertCreated();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->where('data.name', 'Main Menu')
            ->where('data.slug', 'main-menu-crud')
            ->has('data.id')
            ->etc());

        $this->assertDatabaseHas('menus', ['slug' => 'main-menu-crud']);
        $this->assertDatabaseHas('menu_locations', ['location' => 'main-menu']);
        $this->assertDatabaseHas('menu_nodes', ['title' => 'Home']);
    }

    public function testCreateMenuRejectsDuplicateSlug(): void
    {
        Menu::query()->create([
            'name' => 'Main Menu',
            'slug' => 'duplicated-menu',
            'status' => 'published',
        ]);

        $payload = [
            'name' => 'Another Menu',
            'slug' => 'duplicated-menu',
            'status' => 'published',
        ];

        $response = $this->actingAs($this->user, 'api')->postJson(URL::action(CreateMenuController::class), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['slug']);
    }

    public function testFindMenuByIdWithHashedId(): void
    {
        $menu = Menu::query()->create(['name' => 'Find Me', 'slug' => 'find-me', 'status' => 'published']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindMenuByIdController::class, ['id' => $menu->getHashedKey()]));

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->where('data.id', $menu->getHashedKey())
            ->where('data.slug', 'find-me')
            ->etc());
    }

    public function testFindMenuByIdReturnsNotFound(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindMenuByIdController::class, ['id' => hashids()->encode(999999)]));

        $response->assertNotFound();
    }

    public function testUpdateMenu(): void
    {
        $menu = Menu::query()->create(['name' => 'Old', 'slug' => 'menu-old', 'status' => 'draft']);

        $payload = [
            'name' => 'New Name',
            'slug' => 'menu-new',
            'status' => 'published',
            'locations' => ['footer'],
            'nodes' => [
                [
                    'title' => 'Contact',
                    'url' => '/contact',
                    'target' => '_self',
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson(URL::action(UpdateMenuController::class, ['id' => $menu->getHashedKey()]), $payload);

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->where('data.name', 'New Name')
            ->where('data.slug', 'menu-new')
            ->etc());

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'New Name',
            'slug' => 'menu-new',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('menu_locations', ['menu_id' => $menu->id, 'location' => 'footer']);
    }

    public function testUpdateMenuRejectsNodeIdFromAnotherMenu(): void
    {
        $menu = Menu::query()->create(['name' => 'Main', 'slug' => 'main-own', 'status' => 'published']);
        $anotherMenu = Menu::query()->create(['name' => 'Other', 'slug' => 'other-own', 'status' => 'published']);
        $foreignNode = MenuNode::query()->create([
            'menu_id' => $anotherMenu->id,
            'title' => 'Foreign',
            'url' => '/foreign',
            'position' => 0,
        ]);

        $payload = [
            'nodes' => [
                [
                    'id' => $foreignNode->id,
                    'title' => 'Should Fail',
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson(URL::action(UpdateMenuController::class, ['id' => $menu->getHashedKey()]), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['nodes']);
    }

    public function testUpdateMenuRejectsDuplicateNodeIdsInPayload(): void
    {
        $menu = Menu::query()->create(['name' => 'Main', 'slug' => 'main-duplicate-id', 'status' => 'published']);
        $node = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Node',
            'url' => '/node',
            'position' => 0,
        ]);

        $payload = [
            'nodes' => [
                [
                    'id' => $node->id,
                    'title' => 'Parent',
                    'children' => [
                        [
                            'id' => $node->id,
                            'title' => 'Child duplicate',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson(URL::action(UpdateMenuController::class, ['id' => $menu->getHashedKey()]), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['nodes']);
    }

    public function testUpdateMenuDecodesHashedNodeIdFromBody(): void
    {
        $menu = Menu::query()->create(['name' => 'Decode Body', 'slug' => 'decode-body', 'status' => 'published']);
        $node = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Before',
            'url' => '/before',
            'position' => 0,
        ]);

        $payload = [
            'nodes' => [
                [
                    'id' => $node->getHashedKey(),
                    'title' => 'After Decode',
                    'url' => '/after',
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson(URL::action(UpdateMenuController::class, ['id' => $menu->getHashedKey()]), $payload);

        $response->assertOk();
        $this->assertDatabaseHas('menu_nodes', [
            'id' => $node->id,
            'title' => 'After Decode',
            'url' => '/after',
        ]);
    }

    public function testUpdateMenuRejectsParentChildLoopByParentId(): void
    {
        $menu = Menu::query()->create(['name' => 'Loop', 'slug' => 'loop-menu', 'status' => 'published']);
        $nodeA = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'A',
            'url' => '/a',
            'position' => 0,
        ]);
        $nodeB = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'B',
            'url' => '/b',
            'position' => 1,
        ]);

        $payload = [
            'nodes' => [
                [
                    'id' => $nodeA->getHashedKey(),
                    'parent_id' => $nodeB->getHashedKey(),
                    'title' => 'A',
                    'url' => '/a',
                ],
                [
                    'id' => $nodeB->getHashedKey(),
                    'parent_id' => $nodeA->getHashedKey(),
                    'title' => 'B',
                    'url' => '/b',
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson(URL::action(UpdateMenuController::class, ['id' => $menu->getHashedKey()]), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['nodes']);
    }

    public function testDeleteMenu(): void
    {
        $menu = Menu::query()->create(['name' => 'Delete', 'slug' => 'delete-menu', 'status' => 'published']);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson(URL::action(DeleteMenuController::class, ['id' => $menu->getHashedKey()]));

        $response->assertNoContent();
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    public function testDeleteMenuCascadeDeletesLocationsNodesAndTranslations(): void
    {
        $menu = Menu::query()->create(['name' => 'Delete Cascade', 'slug' => 'delete-cascade', 'status' => 'published']);
        $location = MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'main-menu']);
        $node = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Home',
            'url' => '/',
            'position' => 0,
        ]);
        MenuNodeTranslation::query()->create([
            'menu_nodes_id' => $node->id,
            'lang_code' => 'vi',
            'title' => 'Trang chủ',
            'url' => '/trang-chu',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson(URL::action(DeleteMenuController::class, ['id' => $menu->getHashedKey()]));

        $response->assertNoContent();
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
        $this->assertDatabaseMissing('menu_locations', ['id' => $location->id]);
        $this->assertDatabaseMissing('menu_nodes', ['id' => $node->id]);
        $this->assertDatabaseMissing('menu_nodes_translations', [
            'menu_nodes_id' => $node->id,
            'lang_code' => 'vi',
        ]);
    }

    public function testGetMenuOptions(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson(URL::action(GetMenuOptionsController::class));

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->has('data.locations')
            ->has('data.reference_types')
            ->etc());
    }

    public function testUpdateMenuNodeTranslation(): void
    {
        $menu = Menu::query()->create(['name' => 'Menu Translate', 'slug' => 'menu-translate', 'status' => 'published']);
        $node = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Home',
            'url' => '/',
            'position' => 0,
        ]);

        $payload = [
            'lang_code' => 'vi',
            'title' => 'Trang chủ',
            'url' => '/trang-chu',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(URL::action(UpdateMenuNodeTranslationController::class, ['id' => $node->getHashedKey()]), $payload);

        $response->assertOk();
        $this->assertDatabaseHas('menu_nodes_translations', [
            'menu_nodes_id' => $node->id,
            'lang_code' => 'vi',
            'title' => 'Trang chủ',
            'url' => '/trang-chu',
        ]);
    }

    public function testUpdateMenuNodeTranslationRequiresValidLocale(): void
    {
        $menu = Menu::query()->create(['name' => 'Menu Translate', 'slug' => 'menu-translate-required', 'status' => 'published']);
        $node = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Home',
            'url' => '/',
            'position' => 0,
        ]);

        $payload = [
            'lang_code' => 'invalid-lang',
            'title' => 'Trang chủ',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(URL::action(UpdateMenuNodeTranslationController::class, ['id' => $node->getHashedKey()]), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['lang_code']);
    }

    public function testUnauthorizedUserCannotListMenus(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user, 'api')->getJson(URL::action(ListMenusController::class));

        $response->assertForbidden();
    }
}
