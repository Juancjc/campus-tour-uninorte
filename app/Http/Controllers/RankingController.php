<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RankingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $games = Game::query()->where('active', true)->orderBy('display_order')->get();
        $rankings = $games->mapWithKeys(function (Game $game) use ($request): array {
            $query = GameScore::query()
                ->whereBelongsTo($game)
                ->where('completed_count', '>', 0)
                ->join('users', 'users.id', '=', 'game_scores.user_id')
                ->select('game_scores.user_id', 'users.name', 'users.school_name', 'game_scores.best_score', 'game_scores.best_duration_ms', 'game_scores.achieved_at')
                ->orderByDesc('game_scores.best_score')
                ->orderBy('game_scores.best_duration_ms')
                ->orderBy('game_scores.achieved_at')
                ->orderBy('game_scores.user_id');

            $all = $query->get();

            return [$game->slug => [
                'game' => $game->only(['slug', 'name', 'icon']),
                'top' => $this->withPositions($all->take(10)),
                'me' => $this->personalPosition($all, $request->user()->id),
            ]];
        });

        $overall = DB::table('game_scores')
            ->join('users', 'users.id', '=', 'game_scores.user_id')
            ->where('game_scores.completed_count', '>', 0)
            ->select('users.id as user_id', 'users.name', 'users.school_name')
            ->selectRaw('SUM(game_scores.best_score) as total_score')
            ->selectRaw('SUM(COALESCE(game_scores.best_duration_ms, 0)) as total_duration_ms')
            ->selectRaw('MIN(game_scores.achieved_at) as first_achieved_at')
            ->groupBy('users.id', 'users.name', 'users.school_name')
            ->orderByDesc('total_score')
            ->orderBy('total_duration_ms')
            ->orderBy('first_achieved_at')
            ->orderBy('users.id')
            ->get();

        return Inertia::render('Rankings/Index', [
            'rankings' => $rankings->all(),
            'overall' => [
                'top' => $this->withPositions($overall->take(10)),
                'me' => $this->personalPosition($overall, $request->user()->id),
            ],
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function withPositions(Collection $rows): array
    {
        return $rows->values()->map(fn ($row, int $index): array => [...$this->rowToArray($row), 'position' => $index + 1])->all();
    }

    /** @return array<string, mixed>|null */
    private function personalPosition(Collection $rows, int $userId): ?array
    {
        $index = $rows->search(fn ($row): bool => (int) $row->user_id === $userId);

        return $index === false ? null : [...$this->rowToArray($rows[$index]), 'position' => $index + 1];
    }

    /** @return array<string, mixed> */
    private function rowToArray(mixed $row): array
    {
        return $row instanceof Arrayable ? $row->toArray() : (array) $row;
    }
}
