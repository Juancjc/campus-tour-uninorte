<?php

namespace Tests\Feature\Services;

use App\Models\Game;
use App\Models\GameScore;
use App\Models\GameSession;
use App\Models\User;
use App\Services\GameScoreService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GameScoreServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cancelled_session_cannot_restore_points_from_a_stale_request(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'code-runner']);
        $staleSession = GameSession::factory()->for($user)->for($game)->create();
        GameScore::factory()->for($user)->for($game)->create([
            'best_score' => 0,
            'attempts' => 1,
            'completed_count' => 0,
        ]);
        GameSession::query()->whereKey($staleSession->id)->update([
            'status' => 'cancelled',
            'result' => 'ranking_reset',
            'completed_at' => now(),
        ]);

        try {
            app(GameScoreService::class)->complete(
                $user,
                $staleSession,
                ['commands' => ['up', 'up', 'up', 'up', 'right', 'right', 'right', 'right']],
                5000,
            );

            $this->fail('A partida cancelada foi concluída.');
        } catch (ValidationException $exception) {
            $this->assertSame(['session' => ['Esta partida já foi encerrada.']], $exception->errors());
        }

        $this->assertDatabaseHas('game_sessions', [
            'id' => $staleSession->id,
            'status' => 'cancelled',
            'score' => 0,
        ]);
        $this->assertDatabaseHas('game_scores', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'best_score' => 0,
            'completed_count' => 0,
        ]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'points' => 0]);
    }
}
