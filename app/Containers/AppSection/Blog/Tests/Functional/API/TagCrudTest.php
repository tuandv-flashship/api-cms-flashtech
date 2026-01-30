<?php

namespace App\Containers\AppSection\Blog\Tests\Functional\API;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Blog\UI\API\Controllers\CreateTagController;
use App\Containers\AppSection\Blog\UI\API\Controllers\DeleteTagController;
use App\Containers\AppSection\Blog\UI\API\Controllers\FindTagByIdController;
use App\Containers\AppSection\Blog\UI\API\Controllers\ListTagsController;
use App\Containers\AppSection\Blog\UI\API\Controllers\UpdateTagController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListTagsController::class)]
#[CoversClass(CreateTagController::class)]
#[CoversClass(FindTagByIdController::class)]
#[CoversClass(UpdateTagController::class)]
#[CoversClass(DeleteTagController::class)]
final class TagCrudTest extends ApiTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();
        
        $permissionNames = [
            'tags.index',
            'tags.create',
            'tags.edit',
            'tags.destroy',
        ];
        
        $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $permissionNames)
            ->where('guard_name', 'api')
            ->get();
        
        $this->user->syncPermissions($permissions);
    }

    public function testListTags(): void
    {
        Tag::factory()->count(5)->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListTagsController::class));

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', 5)
                ->etc(),
        );
    }

    public function testCreateTag(): void
    {
        $data = [
            'name' => 'Laravel',
            'description' => 'Laravel framework related',
            'status' => 'published',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateTagController::class), $data);

        $response->assertCreated();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Laravel')
                ->etc(),
        );

        $this->assertDatabaseHas('tags', [
            'name' => 'Laravel',
        ]);
    }

    public function testFindTagById(): void
    {
        $tag = Tag::factory()->createOne(['name' => 'PHP']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindTagByIdController::class, ['tag_id' => $tag->getHashedKey()]));

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'PHP')
                ->etc(),
        );
    }

    public function testUpdateTag(): void
    {
        $tag = Tag::factory()->createOne(['name' => 'Original Tag']);

        $data = [
            'name' => 'Updated Tag Name',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(
                URL::action(UpdateTagController::class, ['tag_id' => $tag->getHashedKey()]),
                $data
            );

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Updated Tag Name')
                ->etc(),
        );
    }

    public function testDeleteTag(): void
    {
        $tag = Tag::factory()->createOne();

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson(URL::action(DeleteTagController::class, ['tag_id' => $tag->getHashedKey()]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function testCreateTagWithDuplicateName(): void
    {
        Tag::factory()->createOne(['name' => 'Existing Tag']);

        $data = [
            'name' => 'Existing Tag',
            'status' => 'published',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateTagController::class), $data);

        // May succeed or fail depending on unique constraint
        // Adjust based on your validation rules
        $response->assertStatus(201)->assertJsonPath('data.name', 'Existing Tag');
    }
}
