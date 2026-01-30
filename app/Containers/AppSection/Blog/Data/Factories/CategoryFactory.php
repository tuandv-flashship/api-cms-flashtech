<?php

namespace App\Containers\AppSection\Blog\Data\Factories;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Category
 *
 * @extends ParentFactory<TModel>
 */
final class CategoryFactory extends ParentFactory
{
    /** @var class-string<TModel> */
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'parent_id' => 0,
            'icon' => fake()->optional()->randomElement(['ti ti-folder', 'ti ti-book', 'ti ti-news']),
            'is_featured' => fake()->boolean(20),
            'order' => fake()->numberBetween(0, 100),
            'is_default' => false,
            'status' => ContentStatus::PUBLISHED,
            'author_id' => null,
            'author_type' => null,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::PUBLISHED,
        ]);
    }

    public function draft(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::DRAFT,
        ]);
    }

    public function featured(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function default(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function withParent(Category $parent): self
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    public function withAuthor(User $user): self
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $user->id,
            'author_type' => User::class,
        ]);
    }
}
