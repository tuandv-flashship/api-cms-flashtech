<?php

namespace App\Containers\AppSection\Page\Tests\Functional\API;

use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\App\Containers\AppSection\Page\UI\API\Controllers\ListPagesController::class)]
#[CoversClass(\App\Containers\AppSection\Page\UI\API\Controllers\CreatePageController::class)]
#[CoversClass(\App\Containers\AppSection\Page\UI\API\Controllers\UpdatePageController::class)]
#[CoversClass(\App\Containers\AppSection\Page\UI\API\Controllers\FindPageByIdController::class)]
#[CoversClass(\App\Containers\AppSection\Page\UI\API\Controllers\DeletePageController::class)]
final class PageCrudTest extends ApiTestCase
{
    public function testListPages(): void
    {
        Page::query()->delete();
        Page::create(['name' => 'Page 1', 'status' => 'published']);
        Page::create(['name' => 'Page 2', 'status' => 'published']);
        Page::create(['name' => 'Page 3', 'status' => 'published']);
        
        $user = User::factory()->create();
        $user->givePermissionTo('pages.index');

        $response = $this->actingAs($user, 'api')->getJson('/v1/pages');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function testCreatePage(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('pages.create');

        $data = [
            'name' => 'Test Page',
            'description' => 'Test Description',
            'content' => '<h1>Test Content</h1>',
            'status' => 'published',
            'slug' => 'test-page-slug',
        ];

        $response = $this->actingAs($user, 'api')->postJson('/v1/pages', $data);

        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'Test Page']);
        $this->assertDatabaseHas('pages', ['name' => 'Test Page']);
        // Slug checks would require querying the slugs table, which is outside Page scope for this simple CRUD test
        // or check response if it includes slug
    }

    public function testFindPageById(): void
    {
        $page = Page::create(['name' => 'Find Me', 'status' => 'published']);
        $user = User::factory()->create();
        $user->givePermissionTo('pages.index'); 

        $response = $this->actingAs($user, 'api')->getJson("/v1/pages/{$page->getHashedKey()}");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $page->getHashedKey()]);
    }

    public function testUpdatePage(): void
    {
        $page = Page::create(['name' => 'Original', 'status' => 'published']);
        $user = User::factory()->create();
        $user->givePermissionTo('pages.edit');

        $data = ['name' => 'Updated Page Name'];

        $response = $this->actingAs($user, 'api')->patchJson("/v1/pages/{$page->getHashedKey()}", $data);

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Updated Page Name']);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'name' => 'Updated Page Name']);
    }

    public function testDeletePage(): void
    {
        $page = Page::create(['name' => 'Delete Me', 'status' => 'published']);
        $user = User::factory()->create();
        $user->givePermissionTo('pages.destroy');

        $response = $this->actingAs($user, 'api')->deleteJson("/v1/pages/{$page->getHashedKey()}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function testUnauthorizedUserCannotListPages(): void
    {
        $user = User::factory()->create();
        // No permission

        $response = $this->actingAs($user, 'api')->getJson('/v1/pages');

        $response->assertForbidden();
    }
}
