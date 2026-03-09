<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Skill>
 */
class SkillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced', 'expert']),
            'keywords' => $this->faker->words($this->faker->numberBetween(3, 7)),
        ];
    }

    public function basic(string $basicId): self
    {
        return $this->state([
            'basic_id' => $basicId,
        ]);
    }
}
