<?php

namespace App\Containers\AppSection\AdminMenu\Tests\Functional\API;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Tests\FunctionalTestCase;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\BulkSaveAdminMenuItemsController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\CreateAdminMenuItemController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\DeleteAdminMenuItemController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\FindAdminMenuItemByIdController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\ListAdminMenuItemsController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\RestoreAdminMenuItemController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\UpdateAdminMenuItemController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\UpdateAdminMenuItemTranslationController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(ListAdminMenuItemsController::class)]
#[CoversClass(CreateAdminMenuItemController::class)]
#[CoversClass(FindAdminMenuItemByIdController::class)]
#[CoversClass(UpdateAdminMenuItemController::class)]
#[CoversClass(DeleteAdminMenuItemController::class)]
#[CoversClass(RestoreAdminMenuItemController::class)]
#[CoversClass(BulkSaveAdminMenuItemsController::class)]
#[CoversClass(UpdateAdminMenuItemTranslationController::class)]
final class AdminMenuItemCrudTest extends FunctionalTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->createOne();
        $permissions = Permission::query()->whereIn('name', [
            'admin-menus.index',
            'admin-menus.show',
            'admin-menus.create',
            'admin-menus.update',
            'admin-menus.delete',
        ])->where('guard_name', 'api')->get();

        $this->user->syncPermissions($permissions);
    }

    public function testListAdminMenuItems(): void
    {
        AdminMenuItem::query()->create([
            'key' => 'test-dashboard',
            'name' => 'Dashboard',
            'icon' => 'ti ti-home',
            'route' => '/dashboard',
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListAdminMenuItemsController::class));

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function testCreateAdminMenuItem(): void
    {
        $payload = [
            'key' => 'test-new-item',
            'name' => 'New Item',
            'icon' => 'ti ti-star',
            'route' => '/new-item',
            'permissions' => ['new-item.index'],
            'priority' => 10,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateAdminMenuItemController::class), $payload);

        $response->assertCreated();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->where('data.key', 'test-new-item')
            ->where('data.name', 'New Item')
            ->has('data.id')
            ->etc());

        $this->assertDatabaseHas('admin_menu_items', ['key' => 'test-new-item']);
    }

    public function testCreateChildItem(): void
    {
        $parent = AdminMenuItem::query()->create([
            'key' => 'test-parent',
            'name' => 'Parent',
            'priority' => 1,
        ]);

        $payload = [
            'parent_id' => $parent->getHashedKey(),
            'key' => 'test-child',
            'name' => 'Child',
            'route' => '/child',
            'priority' => 10,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateAdminMenuItemController::class), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('admin_menu_items', [
            'key' => 'test-child',
            'parent_id' => $parent->id,
        ]);
    }

    public function testCreateRejectsExceedingMaxDepth(): void
    {
        config()->set('admin-menu-container.max_depth', 2);

        $root = AdminMenuItem::query()->create([
            'key' => 'depth-root',
            'name' => 'Root',
            'priority' => 1,
        ]);

        $child = AdminMenuItem::query()->create([
            'key' => 'depth-child',
            'name' => 'Child',
            'parent_id' => $root->id,
            'priority' => 1,
        ]);

        // Attempt depth 3 with max_depth=2.
        $payload = [
            'parent_id' => $child->getHashedKey(),
            'key' => 'depth-grandchild',
            'name' => 'Grandchild',
            'priority' => 1,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateAdminMenuItemController::class), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_id']);
    }

    public function testCreateRejectsDuplicateKey(): void
    {
        AdminMenuItem::query()->create([
            'key' => 'duplicate-key',
            'name' => 'Original',
            'priority' => 1,
        ]);

        $payload = [
            'key' => 'duplicate-key',
            'name' => 'Duplicate',
            'priority' => 2,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateAdminMenuItemController::class), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['key']);
    }

    public function testFindAdminMenuItemById(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'find-me',
            'name' => 'Find Me',
            'icon' => 'ti ti-search',
            'route' => '/find-me',
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindAdminMenuItemByIdController::class, ['id' => $item->getHashedKey()]));

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->where('data.id', $item->getHashedKey())
            ->where('data.key', 'find-me')
            ->etc());
    }

    public function testUpdateAdminMenuItem(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'update-me',
            'name' => 'Old Name',
            'priority' => 1,
        ]);

        $payload = [
            'name' => 'New Name',
            'icon' => 'ti ti-edit',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson(URL::action(UpdateAdminMenuItemController::class, ['id' => $item->getHashedKey()]), $payload);

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->where('data.name', 'New Name')
            ->where('data.is_active', false)
            ->etc());

        $this->assertDatabaseHas('admin_menu_items', [
            'id' => $item->id,
            'name' => 'New Name',
            'is_active' => false,
        ]);
    }

    public function testDeleteAdminMenuItem(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'delete-me',
            'name' => 'Delete Me',
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson(URL::action(DeleteAdminMenuItemController::class, ['id' => $item->getHashedKey()]));

        $response->assertNoContent();
        $this->assertSoftDeleted('admin_menu_items', ['id' => $item->id]);
    }

    public function testRestoreAdminMenuItem(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'restore-me',
            'name' => 'Restore Me',
            'priority' => 1,
        ]);
        $item->delete();

        $this->assertSoftDeleted('admin_menu_items', ['id' => $item->id]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(URL::action(RestoreAdminMenuItemController::class, ['id' => $item->getHashedKey()]));

        $response->assertOk();
        $this->assertDatabaseHas('admin_menu_items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    }

    public function testBulkSaveAdminMenuItems(): void
    {
        $payload = [
            'items' => [
                [
                    'key' => 'bulk-root-1',
                    'name' => 'Root 1',
                    'icon' => 'ti ti-home',
                    'route' => '/root-1',
                    'priority' => 1,
                    'children' => [
                        [
                            'key' => 'bulk-child-1',
                            'name' => 'Child 1',
                            'route' => '/child-1',
                            'priority' => 10,
                        ],
                    ],
                ],
                [
                    'key' => 'bulk-root-2',
                    'name' => 'Root 2',
                    'priority' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson(URL::action(BulkSaveAdminMenuItemsController::class), $payload);

        $response->assertOk();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('admin_menu_items', ['key' => 'bulk-root-1']);
        $this->assertDatabaseHas('admin_menu_items', ['key' => 'bulk-child-1']);
        $this->assertDatabaseHas('admin_menu_items', ['key' => 'bulk-root-2']);
    }

    public function testUpdateAdminMenuItemTranslation(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'translate-me',
            'name' => 'Translate Me',
            'priority' => 1,
        ]);

        $payload = [
            'lang_code' => 'vi',
            'name' => 'Dịch cho tôi',
            'description' => 'Mô tả tiếng Việt',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(URL::action(UpdateAdminMenuItemTranslationController::class, ['id' => $item->getHashedKey()]), $payload);

        $response->assertOk();
        $this->assertDatabaseHas('admin_menu_items_translations', [
            'admin_menu_items_id' => $item->id,
            'lang_code' => 'vi',
            'name' => 'Dịch cho tôi',
        ]);
    }

    public function testUnauthorizedUserCannotListAdminMenuItems(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user, 'api')
            ->getJson(URL::action(ListAdminMenuItemsController::class));

        $response->assertForbidden();
    }
}
