<?php

namespace Database\Factories;

use App\Models\AiRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiRequest>
 */
class AiRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid(),
            'provider' => $this->faker->randomElement(['openai', 'anthropic', 'gemini']),
            'prompt' => $this->faker->sentence(),
            'message' => $this->faker->paragraph(),
            'reply' => $this->faker->paragraph(),
            'metadata' => null,
        ];
    }
}
