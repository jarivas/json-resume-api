<?php

namespace Database\Factories;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(4, true),
            'publisher' => $this->faker->company(),
            'releaseDate' => $this->faker->date(),
            'url' => $this->faker->url(),
            'summary' => $this->faker->paragraph(),
        ];
    }
}
