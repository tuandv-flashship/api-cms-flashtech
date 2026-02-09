<?php

namespace App\Containers\AppSection\Gallery\Data\Factories;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Gallery
 * @extends ParentFactory<TModel>
 */
final class GalleryFactory extends ParentFactory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_featured' => fake()->boolean(),
            'order' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(ContentStatus::cases()),
            'author_id' => User::factory(),
            'author_type' => User::class,
        ];
    }
}
