<?php

namespace App\Containers\AppSection\Language\Data\Factories;

use App\Containers\AppSection\Language\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
final class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'lang_name' => $this->faker->unique()->languageCode() . ' Language',
            'lang_locale' => $this->faker->unique()->languageCode(),
            'lang_code' => $this->faker->unique()->languageCode() . '_' . strtoupper($this->faker->countryCode()),
            'lang_flag' => $this->faker->countryCode(),
            'lang_is_default' => false,
            'lang_order' => $this->faker->numberBetween(1, 100),
            'lang_is_rtl' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn(): array => [
            'lang_is_default' => true,
        ]);
    }

    public function rtl(): static
    {
        return $this->state(fn(): array => [
            'lang_is_rtl' => true,
        ]);
    }

    public function vietnamese(): static
    {
        return $this->state(fn(): array => [
            'lang_name' => 'Tiếng Việt',
            'lang_locale' => 'vi',
            'lang_code' => 'vi',
            'lang_flag' => 'vn',
            'lang_is_default' => true,
            'lang_order' => 0,
        ]);
    }

    public function english(): static
    {
        return $this->state(fn(): array => [
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_code' => 'en_US',
            'lang_flag' => 'us',
            'lang_is_default' => false,
            'lang_order' => 1,
        ]);
    }
}
