<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interest>
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

    public function basic(string $basicId): self
    {
        return $this->state([
            'basic_id' => $basicId,
        ]);
    }
}
