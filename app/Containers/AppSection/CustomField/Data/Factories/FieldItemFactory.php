<?php

namespace App\Containers\AppSection\CustomField\Data\Factories;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of FieldItem
 * @extends ParentFactory<TModel>
 */
final class FieldItemFactory extends ParentFactory
{
    protected $model = FieldItem::class;

    public function definition(): array
    {
        return [
            'field_group_id' => FieldGroup::factory(),
            'parent_id' => 0,
            'order' => fake()->numberBetween(0, 50),
            'title' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'type' => fake()->randomElement(['text', 'textarea', 'image', 'number']),
            'instructions' => fake()->sentence(),
            'options' => json_encode([]),
        ];
    }
}
