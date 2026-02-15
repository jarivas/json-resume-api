<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Education>
 */
class EducationFactory extends Factory
{
    protected $model = Education::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-15 years', '-5 years');
        $end = $this->faker->dateTimeBetween($start, '-1 year');

        return [
            'institution' => $this->faker->company(),
            'url' => $this->faker->url(),
            'area' => $this->faker->word(),
            'studyType' => $this->faker->randomElement(['Bachelor', 'Master', 'PhD', 'Certificate']),
            'startDate' => $start->format('Y-m-d H:i:s'),
            'endDate' => $end->format('Y-m-d H:i:s'),
            'score' => $this->faker->word(),
            'summary' => $this->faker->sentence(),
            'courses' => $this->faker->sentences($this->faker->numberBetween(2, 6)),
        ];
    }
}
