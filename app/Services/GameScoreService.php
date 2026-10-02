<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Game;
use App\Models\GameEvent;
use App\Models\GameScore;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class GameScoreService
{
    public function __construct(private GameCatalog $catalog) {}

    public function start(User $user, Game $game): GameSession
    {
        return DB::transaction(function () use ($user, $game): GameSession {
            $score = GameScore::query()->firstOrCreate(
                ['user_id' => $user->id, 'game_id' => $game->id],
                ['best_score' => 0, 'attempts' => 0, 'completed_count' => 0],
            );
            $score->increment('attempts');

            $session = GameSession::query()->create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'attempts' => $score->attempts,
                'status' => 'started',
                'started_at' => now(),
            ]);

            $session->events()->create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'event_type' => 'game_started',
                'level' => 1,
                'payload' => [],
                'occurred_at' => now(),
            ]);

            return $session;
        });
    }

    /** @return array{correct: bool, points: int, explanation: string} */
    public function submitAnswer(User $user, GameSession $session, string $itemId, mixed $answer, int $responseMs): array
    {
        $this->assertOwnership($user, $session);

        return DB::transaction(function () use ($session, $itemId, $answer, $responseMs): array {
            $lockedSession = GameSession::query()->lockForUpdate()->findOrFail($session->id);
            if ($lockedSession->status !== 'started') {
                throw ValidationException::withMessages(['session' => 'Esta partida já foi encerrada.']);
            }

            $alreadyAnswered = $lockedSession->events()->where('event_type', 'answer_submitted')->where('payload->item_id', $itemId)->exists();
            if ($alreadyAnswered) {
                throw ValidationException::withMessages(['item_id' => 'Este desafio já foi respondido.']);
            }

            try {
                $result = $this->catalog->evaluateAnswer($lockedSession->game->slug, $itemId, $answer);
            } catch (InvalidArgumentException) {
                throw ValidationException::withMessages(['item_id' => 'Desafio não encontrado.']);
            }
            $lockedSession->events()->create([
                'user_id' => $lockedSession->user_id,
                'game_id' => $lockedSession->game_id,
                'event_type' => 'answer_submitted',
                'level' => 1,
                'payload' => [
                    'item_id' => $itemId,
                    'correct' => $result['correct'],
                    'points' => $result['points'],
                    'response_ms' => min($responseMs, 300000),
                ],
                'occurred_at' => now(),
            ]);

            return $result;
        });
    }

    /** @return array{score: int, completed: bool, achievements: array<int, array<string, mixed>>, campus_tour_completed: bool} */
    public function complete(User $user, GameSession $session, array $payload, int $durationMs): array
    {
        $this->assertOwnership($user, $session);

        return DB::transaction(function () use ($user, $session, $payload, $durationMs): array {
            $lockedSession = GameSession::query()->with('game')->lockForUpdate()->findOrFail($session->id);
            if ($lockedSession->status === 'completed') {
                return $this->resultPayload($user->fresh(), $lockedSession, []);
            }

            $durationMs = min(max($durationMs, 1000), 3600000);
            if ($lockedSession->game->slug === 'code-runner') {
                $level = max(1, min(5, (int) ($payload['level'] ?? 1)));
                $evaluation = $this->catalog->evaluateCodeRunner($payload['commands'] ?? [], $durationMs, $level);
                $completed = $evaluation['completed'];
                $score = $evaluation['score'];
                $metadata = ['final' => $evaluation['final'], 'commands' => array_slice($payload['commands'] ?? [], 0, 30), 'level' => $level];
            } else {
                $events = $lockedSession->events()->where('event_type', 'answer_submitted')->get();
                $expected = $this->catalog->totalItems($lockedSession->game->slug);
                if ($events->count() < $expected) {
                    throw ValidationException::withMessages(['session' => 'Responda todos os desafios antes de concluir.']);
                }
                $baseScore = $events->sum(fn (GameEvent $event): int => (int) ($event->payload['points'] ?? 0));
                $speedBonus = max(0, 150 - (int) floor($durationMs / 1000));
                $score = min(1000, $baseScore + $speedBonus);
                $completed = true;
                $metadata = ['correct_answers' => $events->filter(fn (GameEvent $event): bool => (bool) ($event->payload['correct'] ?? false))->count()];
            }

            $lockedSession->update([
                'status' => $completed ? 'completed' : 'failed',
                'result' => $completed ? 'success' : 'not_completed',
                'score' => $score,
                'duration_ms' => $durationMs,
                'metadata' => $metadata,
                'completed_at' => now(),
            ]);

            $lockedSession->events()->create([
                'user_id' => $lockedSession->user_id,
                'game_id' => $lockedSession->game_id,
                'event_type' => 'game_completed',
                'level' => 1,
                'payload' => ['completed' => $completed, 'score' => $score],
                'occurred_at' => now(),
            ]);

            $newAchievements = [];
            if ($completed) {
                $gameScore = GameScore::query()->whereBelongsTo($user)->whereBelongsTo($lockedSession->game)->lockForUpdate()->firstOrFail();
                $isBetter = $score > $gameScore->best_score
                    || ($score === $gameScore->best_score && ($gameScore->best_duration_ms === null || $durationMs < $gameScore->best_duration_ms));

                $gameScore->completed_count++;
                if ($isBetter) {
                    $gameScore->best_score = $score;
                    $gameScore->best_duration_ms = $durationMs;
                    $gameScore->achieved_at = now();
                }
                $gameScore->save();

                $newAchievements = $this->awardAchievements($user, $lockedSession->game, $score);
                $user->update(['points' => (int) $this->activeGameScores($user)->sum('best_score')]);
            }

            $campusTourCompleted = $this->hasCompletedCampusTour($user);
            if ($campusTourCompleted && $user->campus_tour_completed_at === null) {
                $user->update(['campus_tour_completed_at' => now()]);
            }

            return $this->resultPayload($user->fresh(), $lockedSession->fresh(), $newAchievements);
        });
    }

    private function assertOwnership(User $user, GameSession $session): void
    {
        if ($session->user_id !== $user->id) {
            throw new AuthorizationException;
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function awardAchievements(User $user, Game $game, int $score): array
    {
        $slugs = ['primeiro-jogo'];
        if ($game->slug === 'code-runner') {
            $slugs[] = 'programador-iniciante';
            if ($score >= 850) {
                $slugs[] = 'mestre-da-logica';
            }
        }
        if ($game->slug === 'guardiao-digital') {
            $slugs[] = 'guardiao-digital';
        }
        if ($this->hasCompletedCampusTour($user)) {
            $slugs[] = 'explorador-da-tecnologia';
        }

        $achievements = Achievement::query()->whereIn('slug', $slugs)->where('active', true)->get();
        $existing = $user->achievements()->pluck('achievements.id');
        $new = $achievements->whereNotIn('id', $existing);

        foreach ($new as $achievement) {
            $user->achievements()->attach($achievement->id, ['earned_at' => now()]);
        }

        return $new->map->only(['slug', 'name', 'description', 'icon', 'points'])->values()->all();
    }

    private function activeGameScores(User $user): HasMany
    {
        return $user->gameScores()->whereHas('game', fn ($query) => $query->where('active', true));
    }

    private function hasCompletedCampusTour(User $user): bool
    {
        $activeGameCount = Game::query()->where('active', true)->count();

        return $activeGameCount > 0
            && $this->activeGameScores($user)->where('completed_count', '>', 0)->count() >= $activeGameCount;
    }

    /** @param array<int, array<string, mixed>> $achievements
     * @return array{score: int, completed: bool, achievements: array<int, array<string, mixed>>, campus_tour_completed: bool}
     */
    private function resultPayload(User $user, GameSession $session, array $achievements): array
    {
        return [
            'score' => $session->score,
            'completed' => $session->status === 'completed',
            'achievements' => $achievements,
            'campus_tour_completed' => $user->campus_tour_completed_at !== null,
        ];
    }
}
