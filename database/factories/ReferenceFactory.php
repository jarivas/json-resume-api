<?php

namespace Database\Factories;

use App\Models\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reference>
 */
class ReferenceFactory extends Factory
{
    protected $model = Reference::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'reference' => $this->faker->sentence(),
        ];
    }
}
