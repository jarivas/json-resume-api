<?php

namespace Database\Factories;

use App\Models\Interest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interest>
 */
class InterestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'keywords' => $this->faker->words($this->faker->numberBetween(3, 7)),
        ];
    }
}
