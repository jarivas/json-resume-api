<?php

namespace Database\Factories;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentConversationMessage>
 */
class AgentConversationMessageFactory extends Factory
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
            'conversation_id' => AgentConversation::factory(),
            'user_id' => null,
            'agent' => 'App\\Ai\\Agents\\ResumeAgent',
            'role' => $this->faker->randomElement(['user', 'assistant']),
            'content' => $this->faker->paragraph(),
            'attachments' => '[]',
            'tool_calls' => '[]',
            'tool_results' => '[]',
            'usage' => '[]',
            'meta' => '[]',
        ];
    }
}
