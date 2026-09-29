<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Achievement;
use App\Models\Game;
use App\Models\GameEvent;
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
        $this->assertDatabaseHas('users', ['id' => $user->id, 'points' => 990]);
        $this->assertDatabaseHas('game_events', ['game_session_id' => $session->id, 'event_type' => 'game_completed']);
    }

    public function test_guardian_digital_accepts_all_situations_and_saves_points(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'guardiao-digital']);

        $startResponse = $this->actingAs($user)->postJson(route('game-sessions.store', $game));
        $startResponse->assertCreated();
        $sessionId = $startResponse->json('session.id');

        $this->postJson(route('game-sessions.events', [$game, $sessionId]), [
            'events' => [
                ['type' => 'game_opened', 'level' => 1, 'payload' => []],
                ['type' => 'level_started', 'level' => 1, 'payload' => []],
            ],
        ])->assertOk()->assertJsonPath('recorded', 2);

        $answers = [
            'phishing' => 'official',
            'password' => 'strong',
            'mfa' => 'second',
            'link' => 'verify',
            'privacy' => 'minimize',
        ];

        foreach ($answers as $itemId => $answer) {
            $this->postJson(route('game-sessions.answer', [$game, $sessionId]), [
                'item_id' => $itemId,
                'answer' => $answer,
                'response_ms' => 1000,
            ])->assertOk()
                ->assertJsonPath('correct', true)
                ->assertJsonPath('points', 200);
        }

        $this->postJson(route('game-sessions.complete', [$game, $sessionId]), [
            'duration_ms' => 5000,
            'payload' => [],
        ])->assertOk()
            ->assertJsonPath('completed', true)
            ->assertJsonPath('score', 1000);

        $this->assertDatabaseHas('game_sessions', ['id' => $sessionId, 'status' => 'completed', 'score' => 1000]);
        $this->assertDatabaseHas('game_scores', ['user_id' => $user->id, 'game_id' => $game->id, 'best_score' => 1000, 'completed_count' => 1]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'points' => 1000]);
        $this->assertSame(5, GameEvent::query()->where('game_session_id', $sessionId)->where('event_type', 'answer_submitted')->count());
    }

    public function test_network_challenge_accepts_sequence_and_choices_and_saves_points(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'rede-em-acao']);

        $startResponse = $this->actingAs($user)->postJson(route('game-sessions.store', $game));
        $startResponse->assertCreated();
        $sessionId = $startResponse->json('session.id');

        $answers = [
            'route' => ['phone', 'wifi', 'router', 'internet', 'server', 'response'],
            'server' => 'process',
            'latency' => 'time',
        ];

        foreach ($answers as $itemId => $answer) {
            $this->postJson(route('game-sessions.answer', [$game, $sessionId]), [
                'item_id' => $itemId,
                'answer' => $answer,
                'response_ms' => 1000,
            ])->assertOk()
                ->assertJsonPath('correct', true);
        }

        $this->postJson(route('game-sessions.complete', [$game, $sessionId]), [
            'duration_ms' => 5000,
            'payload' => [],
        ])->assertOk()
            ->assertJsonPath('completed', true)
            ->assertJsonPath('score', 1000);

        $this->assertDatabaseHas('game_sessions', ['id' => $sessionId, 'status' => 'completed', 'score' => 1000]);
        $this->assertDatabaseHas('game_scores', ['user_id' => $user->id, 'game_id' => $game->id, 'best_score' => 1000, 'completed_count' => 1]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'points' => 1000]);
    }

    public function test_returns_422_for_unknown_challenge_without_recording_answer(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'guardiao-digital']);
        $session = GameSession::factory()->for($user)->for($game)->create();
        GameScore::factory()->for($user)->for($game)->create(['best_score' => 0, 'attempts' => 1, 'completed_count' => 0]);

        $response = $this->actingAs($user)->postJson(route('game-sessions.answer', [$game, $session]), [
            'item_id' => 'inexistente',
            'answer' => 'qualquer',
            'response_ms' => 1000,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.item_id.0', 'Desafio não encontrado.');
        $this->assertDatabaseMissing('game_events', ['game_session_id' => $session->id, 'event_type' => 'answer_submitted']);
    }

    public function test_returns_422_when_game_is_completed_before_all_answers(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'guardiao-digital']);
        $session = GameSession::factory()->for($user)->for($game)->create();
        GameScore::factory()->for($user)->for($game)->create(['best_score' => 0, 'attempts' => 1, 'completed_count' => 0]);

        $response = $this->actingAs($user)->postJson(route('game-sessions.complete', [$game, $session]), [
            'duration_ms' => 5000,
            'payload' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.session.0', 'Responda todos os desafios antes de concluir.');
        $this->assertDatabaseHas('game_sessions', ['id' => $session->id, 'status' => 'started', 'score' => 0]);
        $this->assertDatabaseHas('game_scores', ['user_id' => $user->id, 'game_id' => $game->id, 'best_score' => 0, 'completed_count' => 0]);
    }

    public function test_failed_code_runner_attempt_allows_a_new_session(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'code-runner']);

        $firstStart = $this->actingAs($user)->postJson(route('game-sessions.store', $game));
        $firstStart->assertCreated();
        $firstSessionId = $firstStart->json('session.id');

        $this->postJson(route('game-sessions.complete', [$game, $firstSessionId]), [
            'duration_ms' => 5000,
            'payload' => ['commands' => ['forward']],
        ])->assertOk()
            ->assertJsonPath('completed', false)
            ->assertJsonPath('score', 0);

        $secondStart = $this->postJson(route('game-sessions.store', $game));

        $secondStart->assertCreated()
            ->assertJsonPath('session.status', 'started')
            ->assertJsonPath('session.attempts', 2);
        $this->assertDatabaseHas('game_sessions', ['id' => $firstSessionId, 'status' => 'failed', 'score' => 0]);
        $this->assertDatabaseHas('game_scores', ['user_id' => $user->id, 'game_id' => $game->id, 'attempts' => 2, 'completed_count' => 0]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'points' => 0]);
    }

    public function test_completing_third_game_saves_total_points_and_tour_completion(): void
    {
        $user = User::factory()->create();
        $codeRunner = Game::factory()->create(['slug' => 'code-runner']);
        $guardian = Game::factory()->create(['slug' => 'guardiao-digital']);
        $network = Game::factory()->create(['slug' => 'rede-em-acao']);
        GameScore::factory()->for($user)->for($guardian)->create(['best_score' => 700, 'completed_count' => 1]);
        GameScore::factory()->for($user)->for($network)->create(['best_score' => 800, 'completed_count' => 1]);
        $session = GameSession::factory()->for($user)->for($codeRunner)->create();
        GameScore::factory()->for($user)->for($codeRunner)->create(['best_score' => 0, 'attempts' => 1, 'completed_count' => 0]);
        $achievement = Achievement::factory()->create(['slug' => 'explorador-da-tecnologia']);

        $response = $this->actingAs($user)->postJson(route('game-sessions.complete', [$codeRunner, $session]), [
            'duration_ms' => 5000,
            'payload' => ['commands' => ['forward', 'forward', 'forward', 'forward', 'left', 'forward', 'forward', 'forward', 'forward']],
        ]);

        $response->assertOk()
            ->assertJsonPath('completed', true)
            ->assertJsonPath('score', 990)
            ->assertJsonPath('campus_tour_completed', true)
            ->assertJsonPath('achievements.0.slug', 'explorador-da-tecnologia');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'points' => 2490]);
        $this->assertNotNull($user->fresh()->campus_tour_completed_at);
        $this->assertDatabaseHas('achievement_user', ['user_id' => $user->id, 'achievement_id' => $achievement->id]);
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
