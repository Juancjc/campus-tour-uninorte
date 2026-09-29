<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Game;
use App\Models\GameScore;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GameSessionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_starts_a_game_session(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'code-runner']);

        $response = $this->actingAs($user)->postJson(route('game-sessions.store', $game));

        $response->assertCreated()
            ->assertJsonPath('session.status', 'started')
            ->assertJsonPath('session.attempts', 1);
        $this->assertDatabaseHas('game_sessions', ['user_id' => $user->id, 'game_id' => $game->id, 'status' => 'started']);
        $this->assertDatabaseHas('game_events', ['user_id' => $user->id, 'game_id' => $game->id, 'event_type' => 'game_started']);
    }

    public function test_server_calculates_code_runner_score_and_ignores_client_score(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'code-runner']);
        $session = GameSession::factory()->for($user)->for($game)->create();
        GameScore::factory()->for($user)->for($game)->create(['best_score' => 0, 'attempts' => 1, 'completed_count' => 0]);

        $response = $this->actingAs($user)->postJson(route('game-sessions.complete', [$game, $session]), [
            'score' => 999999,
            'duration_ms' => 5000,
            'payload' => ['commands' => ['forward', 'forward', 'forward', 'forward', 'left', 'forward', 'forward', 'forward', 'forward']],
        ]);

        $response->assertOk()
            ->assertJsonPath('completed', true)
            ->assertJsonPath('score', 990);
        $this->assertDatabaseHas('game_sessions', ['id' => $session->id, 'status' => 'completed', 'score' => 990]);
        $this->assertDatabaseHas('game_scores', ['user_id' => $user->id, 'game_id' => $game->id, 'best_score' => 990, 'completed_count' => 1]);
    }

    public function test_user_cannot_complete_another_users_session(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'code-runner']);
        $session = GameSession::factory()->for($owner)->for($game)->create();

        $response = $this->actingAs($intruder)->postJson(route('game-sessions.complete', [$game, $session]), [
            'duration_ms' => 5000,
            'payload' => ['commands' => ['forward']],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('game_sessions', ['id' => $session->id, 'status' => 'started', 'score' => 0]);
    }
}
