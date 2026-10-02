<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Game;
use App\Models\GameScore;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_ranking_orders_active_game_scores_and_excludes_inactive_game_scores(): void
    {
        $viewer = User::factory()->create(['name' => 'Ana']);
        $leader = User::factory()->create(['name' => 'Bruno']);
        $game = Game::factory()->create(['slug' => 'code-runner', 'display_order' => 1]);
        $inactiveGame = Game::factory()->create(['slug' => 'rede-em-acao', 'active' => false, 'display_order' => 2]);
        GameScore::factory()->for($viewer)->for($game)->create(['best_score' => 700, 'completed_count' => 1, 'achieved_at' => now()]);
        GameScore::factory()->for($leader)->for($game)->create(['best_score' => 900, 'completed_count' => 1, 'achieved_at' => now()->subMinute()]);
        GameScore::factory()->for($viewer)->for($inactiveGame)->create(['best_score' => 1000, 'completed_count' => 1, 'achieved_at' => now()->subMinutes(2)]);

        $response = $this->actingAs($viewer)->get(route('rankings.index'));

        $response->assertOk()->assertInertia(fn (Assert $page): Assert => $page
            ->component('Rankings/Index')
            ->where('rankings.code-runner.top.0.name', 'Bruno')
            ->where('rankings.code-runner.me.position', 2)
            ->where('overall.top.0.name', 'Bruno'));
    }
}
