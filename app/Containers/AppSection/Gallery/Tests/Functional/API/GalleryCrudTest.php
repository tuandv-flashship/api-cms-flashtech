<?php

namespace App\Containers\AppSection\Gallery\Tests\Functional\API;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\Gallery\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Gallery\UI\API\Controllers\CreateGalleryController;
use App\Containers\AppSection\Gallery\UI\API\Controllers\DeleteGalleryController;
use App\Containers\AppSection\Gallery\UI\API\Controllers\FindGalleryByIdController;
use App\Containers\AppSection\Gallery\UI\API\Controllers\ListGalleriesController;
use App\Containers\AppSection\Gallery\UI\API\Controllers\UpdateGalleryController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListGalleriesController::class)]
#[CoversClass(CreateGalleryController::class)]
#[CoversClass(FindGalleryByIdController::class)]
#[CoversClass(UpdateGalleryController::class)]
#[CoversClass(DeleteGalleryController::class)]
final class GalleryCrudTest extends ApiTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();

        $permissions = \Spatie\Permission\Models\Permission::whereIn('name', [
            'galleries.index',
            'galleries.create',
            'galleries.edit',
            'galleries.destroy',
        ])->where('guard_name', 'api')->get();

        $this->user->syncPermissions($permissions);
    }

    public function testListGalleries(): void
    {
        Gallery::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListGalleriesController::class));

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', 3)
                ->etc(),
        );
    }

    public function testListGalleriesWithPagination(): void
    {
        Gallery::factory()->count(15)->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListGalleriesController::class) . '?page=1&limit=5');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', 5)
                ->has('meta.pagination')
                ->where('meta.pagination.total', 15)
                ->etc(),
        );
    }

    public function testCreateGallery(): void
    {
        $data = [
            'name' => 'Test Gallery',
            'description' => 'A test gallery description',
            'status' => 'published',
            'is_featured' => true,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateGalleryController::class), $data);

        $response->assertCreated();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Test Gallery')
                ->where('data.is_featured', true)
                ->etc(),
        );

        $this->assertDatabaseHas('galleries', [
            'name' => 'Test Gallery',
            'is_featured' => true,
        ]);
    }

    public function testCreateGalleryValidationErrors(): void
    {
        $data = [
            'name' => '', // Required
            'description' => '', // Required
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateGalleryController::class), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'description']);
    }

    public function testFindGalleryById(): void
    {
        $gallery = Gallery::factory()->createOne(['name' => 'Find Me Gallery']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindGalleryByIdController::class, ['gallery_id' => $gallery->getHashedKey()]));

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Find Me Gallery')
                ->where('data.id', $gallery->getHashedKey())
                ->etc(),
        );
    }

    public function testFindGalleryByIdNotFound(): void
    {
        $gallery = Gallery::factory()->createOne();
        $hashedKey = $gallery->getHashedKey();
        $gallery->delete();

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindGalleryByIdController::class, ['gallery_id' => $hashedKey]));

        $response->assertNotFound();
    }

    public function testUpdateGallery(): void
    {
        $gallery = Gallery::factory()->createOne(['name' => 'Original Name']);

        $data = [
            'name' => 'Updated Gallery Name',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(
                URL::action(UpdateGalleryController::class, ['gallery_id' => $gallery->getHashedKey()]),
                $data,
            );

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Updated Gallery Name')
                ->etc(),
        );

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'name' => 'Updated Gallery Name',
        ]);
    }

    public function testDeleteGallery(): void
    {
        $gallery = Gallery::factory()->createOne();

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson(URL::action(DeleteGalleryController::class, ['gallery_id' => $gallery->getHashedKey()]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('galleries', [
            'id' => $gallery->id,
        ]);
    }

    public function testUnauthorizedUserCannotListGalleries(): void
    {
        $unauthorizedUser = User::factory()->createOne();

        $response = $this->actingAs($unauthorizedUser, 'api')
            ->getJson(URL::action(ListGalleriesController::class));

        $response->assertForbidden();
    }

    public function testUnauthenticatedUserCannotCreateGallery(): void
    {
        $data = [
            'name' => 'Test',
            'description' => 'Test desc',
            'status' => 'published',
        ];

        $response = $this->postJson(URL::action(CreateGalleryController::class), $data);

        $response->assertUnauthorized();
    }
}
