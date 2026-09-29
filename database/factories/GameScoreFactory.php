<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameScore;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameScore>
 */
class GameScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'best_score' => fake()->numberBetween(100, 1000),
            'best_duration_ms' => fake()->numberBetween(10000, 180000),
            'attempts' => 1,
            'completed_count' => 1,
            'achieved_at' => now(),
        ];
    }
}
