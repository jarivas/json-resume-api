<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-3 years', 'now');
        $end = $this->faker->dateTimeBetween($start, '+2 years');

        return [
            'name' => $this->faker->sentence(3, true),
            'roles' => $this->faker->words($this->faker->numberBetween(2, 6)),
            'startDate' => $start->format('Y-m-d H:i:s'),
            'endDate' => $end->format('Y-m-d H:i:s'),
            'description' => $this->faker->paragraph(),
            'highlights' => $this->faker->sentences($this->faker->numberBetween(2, 6)),
            'url' => $this->faker->url(),
        ];
    }
}
