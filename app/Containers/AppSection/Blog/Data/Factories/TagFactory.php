<?php

namespace App\Containers\AppSection\Blog\Data\Factories;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Tag
 *
 * @extends ParentFactory<TModel>
 */
final class TagFactory extends ParentFactory
{
    /** @var class-string<TModel> */
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->optional()->sentence(),
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

    public function withAuthor(User $user): self
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $user->id,
            'author_type' => User::class,
        ]);
    }
}
