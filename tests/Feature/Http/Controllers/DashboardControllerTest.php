<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Game;
use App\Models\GameScore;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_excludes_inactive_games_from_progress_and_stats(): void
    {
        $user = User::factory()->create();
        $activeGame = Game::factory()->create(['slug' => 'code-runner', 'active' => true]);
        $inactiveGame = Game::factory()->create(['slug' => 'rede-em-acao', 'active' => false]);
        GameScore::factory()->for($user)->for($activeGame)->create([
            'best_score' => 700,
            'attempts' => 2,
            'completed_count' => 1,
        ]);
        GameScore::factory()->for($user)->for($inactiveGame)->create([
            'best_score' => 900,
            'attempts' => 3,
            'completed_count' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->has('games', 1)
            ->where('games.0.slug', 'code-runner')
            ->where('progress.completed', 1)
            ->where('progress.total', 1)
            ->where('progress.percentage', 100)
            ->where('stats.points', 700)
            ->where('stats.attempts', 2));
    }
}
