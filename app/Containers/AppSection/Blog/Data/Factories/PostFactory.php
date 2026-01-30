<?php

namespace App\Containers\AppSection\Blog\Data\Factories;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Post
 *
 * @extends ParentFactory<TModel>
 */
final class PostFactory extends ParentFactory
{
    /** @var class-string<TModel> */
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'image' => fake()->optional()->imageUrl(),
            'is_featured' => fake()->boolean(20),
            'format_type' => null,
            'status' => ContentStatus::PUBLISHED,
            'author_id' => null,
            'author_type' => null,
            'views' => fake()->numberBetween(0, 1000),
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

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::PENDING,
        ]);
    }

    public function featured(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
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
