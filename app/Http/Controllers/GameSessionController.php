<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteGameSessionRequest;
use App\Http\Requests\StoreGameEventsRequest;
use App\Http\Requests\SubmitGameAnswerRequest;
use App\Models\Game;
use App\Models\GameSession;
use App\Services\GameScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GameSessionController extends Controller
{
    public function store(Request $request, Game $game, GameScoreService $scores): JsonResponse
    {
        abort_unless($game->active, 404);
        $session = $scores->start($request->user(), $game);

        return response()->json([
            'session' => $session->only(['id', 'uuid', 'status', 'attempts', 'started_at']),
        ], 201);
    }

    public function answer(SubmitGameAnswerRequest $request, Game $game, GameSession $gameSession, GameScoreService $scores): JsonResponse
    {
        $this->authorizeSession($game, $gameSession);
        $data = $request->validated();

        return response()->json($scores->submitAnswer(
            $request->user(),
            $gameSession,
            $data['item_id'],
            $data['answer'],
            $data['response_ms'],
        ));
    }

    public function events(StoreGameEventsRequest $request, Game $game, GameSession $gameSession): JsonResponse
    {
        $this->authorizeSession($game, $gameSession);
        $events = collect($request->validated('events'))->map(fn (array $event): array => [
            'user_id' => $request->user()->id,
            'game_id' => $game->id,
            'event_type' => $event['type'],
            'level' => $event['level'] ?? null,
            'payload' => $event['payload'] ?? [],
            'occurred_at' => now(),
        ])->all();
        $gameSession->events()->createMany($events);

        return response()->json(['recorded' => count($events)]);
    }

    public function complete(CompleteGameSessionRequest $request, Game $game, GameSession $gameSession, GameScoreService $scores): JsonResponse
    {
        $this->authorizeSession($game, $gameSession);
        $data = $request->validated();

        return response()->json($scores->complete(
            $request->user(),
            $gameSession,
            $data['payload'] ?? [],
            $data['duration_ms'],
        ));
    }

    private function authorizeSession(Game $game, GameSession $gameSession): void
    {
        abort_unless($gameSession->game_id === $game->id, 404);
        Gate::authorize('update', $gameSession);
    }
}
