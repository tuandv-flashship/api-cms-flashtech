<?php

namespace App\Containers\AppSection\AdminMenu\Tests\Functional\API;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Models\AdminMenuItemTranslation;
use App\Containers\AppSection\AdminMenu\Tests\FunctionalTestCase;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\FindAdminMenuItemByIdController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\ListAdminMenuItemsController;
use App\Containers\AppSection\AdminMenu\UI\API\Controllers\ListAdminMenuSectionsController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(ListAdminMenuItemsController::class)]
#[CoversClass(FindAdminMenuItemByIdController::class)]
#[CoversClass(ListAdminMenuSectionsController::class)]
final class AdminMenuSectionAndTranslationTest extends FunctionalTestCase
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
        ])->where('guard_name', 'api')->get();

        $this->user->syncPermissions($permissions);
    }

    // ─── Section Grouping ───

    public function testListReturnsSectionsForPanelParent(): void
    {
        $parent = AdminMenuItem::query()->create([
            'key' => 'test-settings',
            'name' => 'Settings',
            'children_display' => 'panel',
            'priority' => 1,
        ]);

        AdminMenuItem::query()->create([
            'key' => 'test-lang',
            'name' => 'Languages',
            'parent_id' => $parent->id,
            'section' => 'Localization',
            'priority' => 10,
        ]);

        AdminMenuItem::query()->create([
            'key' => 'test-trans',
            'name' => 'Translations',
            'parent_id' => $parent->id,
            'section' => 'Localization',
            'priority' => 20,
        ]);

        AdminMenuItem::query()->create([
            'key' => 'test-users',
            'name' => 'Users',
            'parent_id' => $parent->id,
            'section' => 'System',
            'priority' => 30,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListAdminMenuItemsController::class));

        $response->assertOk();

        // Panel parent should have both children and sections
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->has('data', 1)
            ->has('data.0.children', 3)
            ->has('data.0.sections', 2)
            ->where('data.0.sections.0.name', 'Localization')
            ->has('data.0.sections.0.items', 2)
            ->where('data.0.sections.1.name', 'System')
            ->has('data.0.sections.1.items', 1)
            ->etc());
    }

    public function testListDoesNotReturnSectionsForSidebarParent(): void
    {
        $parent = AdminMenuItem::query()->create([
            'key' => 'test-blog',
            'name' => 'Blog',
            'children_display' => 'sidebar',
            'priority' => 1,
        ]);

        AdminMenuItem::query()->create([
            'key' => 'test-posts',
            'name' => 'Posts',
            'parent_id' => $parent->id,
            'section' => 'Content',
            'priority' => 10,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListAdminMenuItemsController::class));

        $response->assertOk();

        // Sidebar parent should have children but NOT sections
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->has('data.0.children', 1)
            ->missing('data.0.sections')
            ->etc());
    }

    public function testSectionFieldInResponse(): void
    {
        AdminMenuItem::query()->create([
            'key' => 'test-item',
            'name' => 'Test',
            'section' => 'MySection',
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListAdminMenuItemsController::class));

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->where('data.0.section', 'MySection')
            ->etc());
    }

    // ─── Include Translations (List) ───

    public function testListWithIncludeTranslations(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'test-translate',
            'name' => 'Translate Me',
            'section' => 'General',
            'priority' => 1,
        ]);

        AdminMenuItemTranslation::query()->create([
            'admin_menu_items_id' => $item->id,
            'lang_code' => 'vi',
            'name' => 'Dịch cho tôi',
            'description' => 'Mô tả vi',
            'section' => 'Chung',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListAdminMenuItemsController::class) . '?include=translations');

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->has('data.0.translations.vi')
            ->where('data.0.translations.vi.name', 'Dịch cho tôi')
            ->where('data.0.translations.vi.description', 'Mô tả vi')
            ->where('data.0.translations.vi.section', 'Chung')
            ->etc());
    }

    public function testListWithoutIncludeTranslationsDoesNotShowTranslations(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'test-no-trans',
            'name' => 'No Trans',
            'priority' => 1,
        ]);

        AdminMenuItemTranslation::query()->create([
            'admin_menu_items_id' => $item->id,
            'lang_code' => 'vi',
            'name' => 'Không dịch',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListAdminMenuItemsController::class));

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->missing('data.0.translations')
            ->etc());
    }

    // ─── Include Translations (Find) ───

    public function testFindWithIncludeTranslations(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'test-find-trans',
            'name' => 'Find Trans',
            'section' => 'General',
            'priority' => 1,
        ]);

        AdminMenuItemTranslation::query()->create([
            'admin_menu_items_id' => $item->id,
            'lang_code' => 'vi',
            'name' => 'Tìm bản dịch',
            'description' => 'Mô tả',
            'section' => 'Chung',
        ]);

        $url = URL::action(FindAdminMenuItemByIdController::class, ['id' => $item->getHashedKey()]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson($url . '?include=translations');

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->has('data.translations.vi')
            ->where('data.translations.vi.name', 'Tìm bản dịch')
            ->where('data.translations.vi.section', 'Chung')
            ->etc());
    }

    public function testFindWithoutIncludeDoesNotShowTranslations(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'test-find-no-trans',
            'name' => 'No Trans',
            'priority' => 1,
        ]);

        AdminMenuItemTranslation::query()->create([
            'admin_menu_items_id' => $item->id,
            'lang_code' => 'vi',
            'name' => 'Không hiện',
        ]);

        $url = URL::action(FindAdminMenuItemByIdController::class, ['id' => $item->getHashedKey()]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson($url);

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->missing('data.translations')
            ->etc());
    }

    // ─── Sections Endpoint ───

    public function testListSectionsGroupedByParent(): void
    {
        $settings = AdminMenuItem::query()->create([
            'key' => 'test-settings-2',
            'name' => 'Settings',
            'children_display' => 'panel',
            'priority' => 1,
        ]);

        AdminMenuItem::query()->create([
            'key' => 'test-lang-2',
            'name' => 'Languages',
            'parent_id' => $settings->id,
            'section' => 'Localization',
            'priority' => 10,
        ]);

        AdminMenuItem::query()->create([
            'key' => 'test-trans-2',
            'name' => 'Translations',
            'parent_id' => $settings->id,
            'section' => 'Localization',
            'priority' => 20,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/admin-menus/sections');

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->has('data', 1)
            ->where('data.0.id', 'test-settings-2')
            ->where('data.0.title', 'Settings')
            ->has('data.0.sections', 1)
            ->where('data.0.sections.0', 'Localization')
            ->etc());
    }

    public function testListSectionsExcludesSidebarParents(): void
    {
        $sidebar = AdminMenuItem::query()->create([
            'key' => 'test-sidebar',
            'name' => 'Sidebar',
            'children_display' => 'sidebar',
            'priority' => 1,
        ]);

        AdminMenuItem::query()->create([
            'key' => 'test-child-sidebar',
            'name' => 'Child',
            'parent_id' => $sidebar->id,
            'section' => 'SomeSection',
            'priority' => 10,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/admin-menus/sections');

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->has('data', 0)
            ->etc());
    }

    // ─── Create/Update with Section ───

    public function testCreateItemWithSection(): void
    {
        $payload = [
            'key' => 'test-with-section',
            'name' => 'With Section',
            'section' => 'MyGroup',
            'priority' => 1,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/admin-menus', $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('admin_menu_items', [
            'key' => 'test-with-section',
            'section' => 'MyGroup',
        ]);
    }

    public function testUpdateItemSection(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'test-update-section',
            'name' => 'Update Section',
            'section' => 'OldSection',
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->putJson('/v1/admin-menus/' . $item->getHashedKey(), [
                'section' => 'NewSection',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('admin_menu_items', [
            'id' => $item->id,
            'section' => 'NewSection',
        ]);
    }

    public function testUpdateTranslationWithSection(): void
    {
        $item = AdminMenuItem::query()->create([
            'key' => 'test-trans-section',
            'name' => 'Trans Section',
            'section' => 'General',
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson('/v1/admin-menus/' . $item->getHashedKey() . '/translations', [
                'lang_code' => 'vi',
                'name' => 'Bản dịch',
                'section' => 'Chung',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('admin_menu_items_translations', [
            'admin_menu_items_id' => $item->id,
            'lang_code' => 'vi',
            'name' => 'Bản dịch',
            'section' => 'Chung',
        ]);
    }
}
