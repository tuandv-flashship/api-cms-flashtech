<?php

namespace App\Containers\AppSection\CustomField\Data\Factories;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of FieldGroup
 * @extends ParentFactory<TModel>
 */
final class FieldGroupFactory extends ParentFactory
{
    protected $model = FieldGroup::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'rules' => json_encode([]),
            'order' => fake()->numberBetween(0, 50),
            'status' => fake()->randomElement(ContentStatus::cases()),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
