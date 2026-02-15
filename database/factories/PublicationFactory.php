<?php

namespace Database\Factories;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publication>
 */
class PublicationFactory extends Factory
{
    protected $model = Publication::class;

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
