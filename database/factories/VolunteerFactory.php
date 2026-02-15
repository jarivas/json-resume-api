<?php

namespace Database\Factories;

use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Volunteer>
 */
class VolunteerFactory extends Factory
{
    protected $model = Volunteer::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-8 years', '-1 year');
        $end = $this->faker->dateTimeBetween($start, 'now');

        return [
            'organization' => $this->faker->company(),
            'position' => $this->faker->jobTitle(),
            'url' => $this->faker->url(),
            'startDate' => $start->format('Y-m-d H:i:s'),
            'endDate' => $end->format('Y-m-d H:i:s'),
            'summary' => $this->faker->sentence(),
            'highlights' => $this->faker->sentences($this->faker->numberBetween(0, 5)),
        ];
    }
}
