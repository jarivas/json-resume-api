<?php

namespace Database\Factories;

use App\Models\Award;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Award>
 */
class AwardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3, true),
            'date' => $this->faker->date(),
            'awarder' => $this->faker->company(),
            'summary' => $this->faker->sentence(),
        ];
    }
}
