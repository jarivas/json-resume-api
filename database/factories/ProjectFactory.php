<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-3 years', 'now');
        $end = $this->faker->dateTimeBetween($start, '+2 years');

        return [
            'name' => $this->faker->sentence(3, true),
            'startDate' => $start->format('Y-m-d H:i:s'),
            'endDate' => $end->format('Y-m-d H:i:s'),
            'description' => $this->faker->paragraph(),
            'highlights' => $this->faker->sentences($this->faker->numberBetween(2, 6)),
            'url' => $this->faker->url(),
        ];
    }
}
