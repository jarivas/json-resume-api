<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Language>
 */
class LanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'language' => $this->faker->languageCode(),
            'fluency' => $this->faker->randomElement(['native', 'fluent', 'conversational', 'basic']),
        ];
    }
}
