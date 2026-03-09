<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reference>
 */
class ReferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'reference' => $this->faker->sentence(),
        ];
    }

    public function basic(string $basicId): self
    {
        return $this->state([
            'basic_id' => $basicId,
        ]);
    }
}
