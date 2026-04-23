<?php

namespace Database\Factories;

use App\Models\AgentConversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentConversation>
 */
class AgentConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => null,
            'title' => $this->faker->sentence(3),
        ];
    }
}
