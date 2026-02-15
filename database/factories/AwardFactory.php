<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Award;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Award>
 */
class AwardFactory extends Factory
{
    protected $model = Award::class;

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
