<?php

namespace App\Containers\AppSection\Blog\Tests\Functional\API;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Blog\UI\API\Controllers\CreateCategoryController;
use App\Containers\AppSection\Blog\UI\API\Controllers\DeleteCategoryController;
use App\Containers\AppSection\Blog\UI\API\Controllers\FindCategoryByIdController;
use App\Containers\AppSection\Blog\UI\API\Controllers\ListCategoriesController;
use App\Containers\AppSection\Blog\UI\API\Controllers\UpdateCategoryController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListCategoriesController::class)]
#[CoversClass(CreateCategoryController::class)]
#[CoversClass(FindCategoryByIdController::class)]
#[CoversClass(UpdateCategoryController::class)]
#[CoversClass(DeleteCategoryController::class)]
final class CategoryCrudTest extends ApiTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();
        
        $permissionNames = [
            'categories.index',
            'categories.create',
            'categories.edit',
            'categories.destroy',
        ];
        
        $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $permissionNames)
            ->where('guard_name', 'api')
            ->get();
        
        $this->user->syncPermissions($permissions);
    }

    public function testListCategories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(ListCategoriesController::class));

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data', 3)
                ->etc(),
        );
    }

    public function testCreateCategory(): void
    {
        $data = [
            'name' => 'Technology',
            'description' => 'Tech related articles',
            'status' => 'published',
            'is_featured' => true,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateCategoryController::class), $data);

        $response->assertCreated();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Technology')
                ->where('data.is_featured', true)
                ->etc(),
        );

        $this->assertDatabaseHas('categories', [
            'name' => 'Technology',
            'is_featured' => true,
        ]);
    }

    public function testCreateNestedCategory(): void
    {
        $parent = Category::factory()->createOne(['name' => 'Parent Category']);

        $data = [
            'name' => 'Child Category',
            'parent_id' => $parent->getHashedKey(),
            'status' => 'published',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson(URL::action(CreateCategoryController::class), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.parent_id', $parent->getHashedKey());
    }

    public function testFindCategoryById(): void
    {
        $category = Category::factory()->createOne(['name' => 'Find Me']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson(URL::action(FindCategoryByIdController::class, ['category_id' => $category->getHashedKey()]));

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Find Me')
                ->etc(),
        );
    }

    public function testFindCategoryByIdWithParentIncludeDoesNotTriggerLazyLoading(): void
    {
        $parent = Category::factory()->createOne(['name' => 'Parent']);
        $category = Category::factory()->withParent($parent)->createOne(['name' => 'Child']);

        $wasPreventingLazyLoading = Model::preventsLazyLoading();
        Model::preventLazyLoading();

        try {
            $response = $this->actingAs($this->user, 'api')
                ->getJson(
                    URL::action(FindCategoryByIdController::class, ['category_id' => $category->getHashedKey()])
                    . '?include=parent'
                );

            $response->assertOk();
            $response->assertJson(
                static fn (AssertableJson $json): AssertableJson => $json
                    ->has('data')
                    ->has('data.parent')
                    ->where('data.id', $category->getHashedKey())
                    ->etc(),
            );
        } finally {
            Model::preventLazyLoading($wasPreventingLazyLoading);
        }
    }

    public function testUpdateCategory(): void
    {
        $category = Category::factory()->createOne(['name' => 'Original']);

        $data = [
            'name' => 'Updated Name',
            'is_featured' => true,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson(
                URL::action(UpdateCategoryController::class, ['category_id' => $category->getHashedKey()]),
                $data
            );

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->where('data.name', 'Updated Name')
                ->etc(),
        );
    }

    public function testDeleteCategory(): void
    {
        $category = Category::factory()->createOne();

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson(URL::action(DeleteCategoryController::class, ['category_id' => $category->getHashedKey()]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function testDeleteCategoryWithChildrenDeletesChildren(): void
    {
        $parent = Category::factory()->createOne();
        $child = Category::factory()->withParent($parent)->createOne();

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson(URL::action(DeleteCategoryController::class, ['category_id' => $parent->getHashedKey()]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('categories', ['id' => $parent->id]);
        $this->assertDatabaseMissing('categories', ['id' => $child->id]);
    }
}
