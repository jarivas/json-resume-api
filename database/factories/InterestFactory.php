<?php

namespace Database\Factories;

use App\Models\Interest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interest>
 */
class InterestFactory extends Factory
{
    protected $model = Interest::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'keywords' => $this->faker->words($this->faker->numberBetween(3, 7)),
        ];
    }
}
