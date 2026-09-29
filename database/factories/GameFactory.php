<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'category' => 'Tecnologia',
            'description' => fake()->sentence(),
            'icon' => 'pi pi-play',
            'max_score' => 1000,
            'display_order' => fake()->unique()->numberBetween(1, 999),
            'active' => true,
        ];
    }
}
