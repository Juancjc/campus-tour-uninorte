<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\AdminAuditLog;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_without_resetting_ranking(): void
    {
        $score = GameScore::factory()->create(['best_score' => 700]);

        $response = $this->delete(route('admin.rankings.destroy'));

        $response->assertRedirectToRoute('login');
        $this->assertModelExists($score);
    }

    public function test_regular_user_is_forbidden_without_resetting_ranking(): void
    {
        $user = User::factory()->create(['points' => 700]);
        $score = GameScore::factory()->for($user)->create(['best_score' => 700]);

        $response = $this->actingAs($user)->delete(route('admin.rankings.destroy'));

        $response->assertForbidden();
        $this->assertModelExists($score);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'points' => 700]);
    }

    public function test_admin_resets_ranking_and_cancels_started_sessions(): void
    {
        $admin = User::factory()->admin()->create(['points' => 200]);
        $user = User::factory()->create([
            'points' => 700,
            'campus_tour_completed_at' => now(),
        ]);
        $game = Game::factory()->create(['slug' => 'code-runner']);
        GameScore::factory()->for($user)->for($game)->create(['best_score' => 700]);
        $startedSession = GameSession::factory()->for($user)->for($game)->create();
        $completedSession = GameSession::factory()->for($user)->for($game)->create([
            'status' => 'completed',
            'result' => 'success',
            'score' => 700,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.rankings.destroy'));

        $response->assertRedirectToRoute('admin.dashboard')
            ->assertSessionHas('success', 'Ranking zerado: 1 placares removidos e 2 usuários atualizados.');
        $this->assertDatabaseCount('game_scores', 0);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'points' => 0,
            'campus_tour_completed_at' => null,
        ]);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'points' => 0]);
        $this->assertDatabaseHas('game_sessions', [
            'id' => $startedSession->id,
            'status' => 'cancelled',
            'result' => 'ranking_reset',
        ]);
        $this->assertDatabaseHas('game_sessions', [
            'id' => $completedSession->id,
            'status' => 'completed',
            'result' => 'success',
            'score' => 700,
        ]);

        $auditLog = AdminAuditLog::query()->where('action', 'reset_ranking')->sole();
        $this->assertSame($admin->id, $auditLog->admin_id);
        $this->assertSame('ranking', $auditLog->entity);
        $this->assertSame([
            'scores_deleted' => 1,
            'sessions_cancelled' => 1,
            'users_reset' => 2,
        ], $auditLog->metadata);
    }
}
