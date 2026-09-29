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

    public function test_ranking_orders_higher_score_first_and_includes_personal_position(): void
    {
        $viewer = User::factory()->create(['name' => 'Ana']);
        $leader = User::factory()->create(['name' => 'Bruno']);
        $game = Game::factory()->create(['slug' => 'code-runner', 'display_order' => 1]);
        GameScore::factory()->for($viewer)->for($game)->create(['best_score' => 700, 'completed_count' => 1, 'achieved_at' => now()]);
        GameScore::factory()->for($leader)->for($game)->create(['best_score' => 900, 'completed_count' => 1, 'achieved_at' => now()->subMinute()]);

        $response = $this->actingAs($viewer)->get(route('rankings.index'));

        $response->assertOk()->assertInertia(fn (Assert $page): Assert => $page
            ->component('Rankings/Index')
            ->where('rankings.code-runner.top.0.name', 'Bruno')
            ->where('rankings.code-runner.me.position', 2)
            ->where('overall.top.0.name', 'Bruno'));
    }
}
