<?php

namespace Database\Factories;

use App\Models\GameEvent;
use App\Models\GameSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameEvent>
 */
class GameEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fn (array $attributes): int => GameSession::findOrFail($attributes['game_session_id'])->user_id,
            'game_id' => fn (array $attributes): int => GameSession::findOrFail($attributes['game_session_id'])->game_id,
            'game_session_id' => GameSession::factory(),
            'event_type' => 'game_started',
            'payload' => [],
            'occurred_at' => now(),
        ];
    }
}
