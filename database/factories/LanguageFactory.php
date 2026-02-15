<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'language' => $this->faker->languageCode(),
            'fluency' => $this->faker->randomElement(['native', 'fluent', 'conversational', 'basic']),
        ];
    }
}
