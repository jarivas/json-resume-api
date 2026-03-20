<?php

namespace Database\Factories;

use App\Models\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reference>
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
}
