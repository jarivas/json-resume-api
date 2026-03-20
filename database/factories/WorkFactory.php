<?php

namespace Database\Factories;

use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Work>
 */
class WorkFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-10 years', '-1 year');
        $end = $this->faker->dateTimeBetween($start, 'now');

        return [
            'name' => $this->faker->company(),
            'position' => $this->faker->jobTitle(),
            'url' => $this->faker->url(),
            'startDate' => $start->format('Y-m-d H:i:s'),
            'endDate' => $end->format('Y-m-d H:i:s'),
            'summary' => $this->faker->paragraph(),
            'highlights' => $this->faker->sentences($this->faker->numberBetween(1, 5)),
        ];
    }
}
