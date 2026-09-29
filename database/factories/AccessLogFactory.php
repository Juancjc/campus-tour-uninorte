<?php

namespace Database\Factories;

use App\Models\AccessLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessLog>
 */
class AccessLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visitor_uuid' => fake()->uuid(),
            'session_id' => fake()->uuid(),
            'ip_address' => fake()->ipv4(),
            'method' => 'GET',
            'url' => fake()->url(),
            'route_name' => 'home',
            'user_agent' => fake()->userAgent(),
            'browser' => 'Chrome',
            'operating_system' => 'Windows',
            'device' => 'Desktop',
            'device_type' => 'desktop',
            'language' => 'pt-BR',
            'status_code' => 200,
            'duration_ms' => fake()->numberBetween(5, 500),
            'is_authenticated' => false,
        ];
    }
}
