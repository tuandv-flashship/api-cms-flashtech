<?php

namespace App\Containers\AppSection\Blog\Tests\Functional\API;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Blog\UI\API\Controllers\CreatePostController;
use App\Containers\AppSection\Blog\UI\API\Controllers\DeletePostController;
use App\Containers\AppSection\Blog\UI\API\Controllers\FindPostByIdController;
use App\Containers\AppSection\Blog\UI\API\Controllers\ListPostsController;
use App\Containers\AppSection\Blog\UI\API\Controllers\UpdatePostController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListPostsController::class)]
#[CoversClass(CreatePostController::class)]
#[CoversClass(FindPostByIdController::class)]
#[CoversClass(UpdatePostController::class)]
#[CoversClass(DeletePostController::class)]
final class PostCrudTest extends ApiTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();
        
        // Get permissions from ContainerTestCase and assign to user
        $permissionNames = [
            'posts.index',
            'posts.create',
            'posts.edit',
            'posts.destroy',
        ];
        
        $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $permissionNames)
            ->where('guard_name', 'api')
            ->get();
        
        $this->user->syncPermissions($permissions);
    }

    public function testListPosts(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListPostsController::class));

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', 3)
                ->etc(),
        );
    }

    public function testListPostsWithPagination(): void
    {
        Post::factory()->count(15)->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListPostsController::class) . '?page=1&limit=5');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', 5)
                ->has('meta.pagination')
                ->where('meta.pagination.total', 15)
                ->etc(),
        );
    }

    public function testListPostsWithAuthorIncludeDoesNotTriggerLazyLoading(): void
    {
        Post::factory()
            ->count(3)
            ->withAuthor($this->user)
            ->create();

        $wasPreventingLazyLoading = Model::preventsLazyLoading();
        Model::preventLazyLoading();

        try {
            $response = $this->actingAs($this->user, 'api')
                ->getJson(URL::action(ListPostsController::class) . '?include=author');

            $response->assertOk();
            $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', 3)
                ->has('data.0.author')
                ->etc());
        } finally {
            Model::preventLazyLoading($wasPreventingLazyLoading);
        }
    }

    public function testCreatePost(): void
    {
        $category = Category::factory()->createOne();
        $tag = Tag::factory()->createOne();

        $data = [
            'name' => 'Test Post Title',
            'description' => 'Test post description',
            'content' => '<p>Test post content</p>',
            'status' => 'published',
            'is_featured' => true,
            'category_ids' => [$category->getHashedKey()],
            'tag_ids' => [$tag->getHashedKey()],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreatePostController::class), $data);

        $response->assertCreated();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Test Post Title')
                ->where('data.description', 'Test post description')
                ->where('data.is_featured', true)
                ->etc(),
        );

        $this->assertDatabaseHas('posts', [
            'name' => 'Test Post Title',
            'is_featured' => true,
        ]);
        $this->assertDatabaseHas('slugs', [
            'key' => 'test-post-title',
        ]);
    }

    public function testCreatePostWithTagNames(): void
    {
        $data = [
            'name' => 'Post With New Tags',
            'content' => 'Content here',
            'status' => 'published',
            'tag_names' => ['new-tag-1', 'new-tag-2'],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreatePostController::class), $data);

        $response->assertCreated();

        $this->assertDatabaseHas('tags', ['name' => 'new-tag-1']);
        $this->assertDatabaseHas('tags', ['name' => 'new-tag-2']);
    }

    public function testFindPostById(): void
    {
        $post = Post::factory()->createOne(['name' => 'Find Me Post']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindPostByIdController::class, ['post_id' => $post->getHashedKey()]));

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Find Me Post')
                ->where('data.id', $post->getHashedKey())
                ->etc(),
        );
    }

    public function testFindPostByIdWithAuthorIncludeDoesNotTriggerLazyLoading(): void
    {
        $post = Post::factory()
            ->withAuthor($this->user)
            ->createOne(['name' => 'Find Post With Author']);

        $wasPreventingLazyLoading = Model::preventsLazyLoading();
        Model::preventLazyLoading();

        try {
            $response = $this->actingAs($this->user, 'api')
                ->getJson(
                    URL::action(FindPostByIdController::class, ['post_id' => $post->getHashedKey()])
                    . '?include=author'
                );

            $response->assertOk();
            $response->assertJson(
                static fn (AssertableJson $json): AssertableJson => $json
                    ->has('data')
                    ->has('data.author')
                    ->where('data.id', $post->getHashedKey())
                    ->etc(),
            );
        } finally {
            Model::preventLazyLoading($wasPreventingLazyLoading);
        }
    }

    public function testFindPostByIdWithoutAuthorIncludeDoesNotTriggerLazyLoading(): void
    {
        $post = Post::factory()
            ->withAuthor($this->user)
            ->createOne(['name' => 'Find Post Without Author Include']);

        $wasPreventingLazyLoading = Model::preventsLazyLoading();
        Model::preventLazyLoading();

        try {
            $response = $this->actingAs($this->user, 'api')
                ->getJson(URL::action(FindPostByIdController::class, ['post_id' => $post->getHashedKey()]));

            $response->assertOk();
            $response->assertJson(
                static fn (AssertableJson $json): AssertableJson => $json
                    ->has('data')
                    ->where('data.id', $post->getHashedKey())
                    ->etc(),
            );
        } finally {
            Model::preventLazyLoading($wasPreventingLazyLoading);
        }
    }

    public function testFindPostByIdNotFound(): void
    {
        // Create and delete a post to get a valid hash that doesn't exist
        $post = Post::factory()->createOne();
        $hashedKey = $post->getHashedKey();
        $post->delete();

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindPostByIdController::class, ['post_id' => $hashedKey]));

        $response->assertNotFound();
    }

    public function testUpdatePost(): void
    {
        $post = Post::factory()->createOne(['name' => 'Original Title']);

        $data = [
            'name' => 'Updated Title',
            'description' => 'Updated description',
            'is_featured' => true,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(
                URL::action(UpdatePostController::class, ['post_id' => $post->getHashedKey()]),
                $data
            );

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Updated Title')
                ->where('data.description', 'Updated description')
                ->etc(),
        );

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'name' => 'Updated Title',
        ]);
    }

    public function testUpdatePostWithEmptyTagIdsClearsTags(): void
    {
        $post = Post::factory()->createOne();
        $tags = Tag::factory()->count(2)->create();
        $post->tags()->sync($tags->pluck('id')->all());

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(
                URL::action(UpdatePostController::class, ['post_id' => $post->getHashedKey()]),
                ['tag_ids' => []]
            );

        $response->assertOk();
        $this->assertCount(0, $post->fresh()->tags);
    }

    public function testUpdatePostWithEmptyTagNamesClearsTags(): void
    {
        $post = Post::factory()->createOne();
        $tags = Tag::factory()->count(2)->create();
        $post->tags()->sync($tags->pluck('id')->all());

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(
                URL::action(UpdatePostController::class, ['post_id' => $post->getHashedKey()]),
                ['tag_names' => []]
            );

        $response->assertOk();
        $this->assertCount(0, $post->fresh()->tags);
    }

    public function testDeletePost(): void
    {
        $post = Post::factory()->createOne();

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson(URL::action(DeletePostController::class, ['post_id' => $post->getHashedKey()]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function testUnauthorizedUserCannotListPosts(): void
    {
        $unauthorizedUser = User::factory()->createOne();

        $response = $this->actingAs($unauthorizedUser, 'api')
            ->getJson(URL::action(ListPostsController::class));

        $response->assertForbidden();
    }

    public function testUnauthenticatedUserCannotCreatePost(): void
    {
        $data = [
            'name' => 'Test Post',
            'content' => 'Content',
            'status' => 'published',
        ];

        $response = $this->postJson(URL::action(CreatePostController::class), $data);

        $response->assertUnauthorized();
    }

    public function testCreatePostValidationErrors(): void
    {
        $data = [
            'name' => '', // Required
            'status' => 'invalid-status', // Invalid enum
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreatePostController::class), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'status']);
    }
}
