<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameScore;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /** @return array<string, mixed> */
    public function for(User $user): array
    {
        $games = Game::query()
            ->where('active', true)
            ->orderBy('display_order')
            ->with(['scores' => fn ($query) => $query->where('user_id', $user->id)])
            ->get()
            ->map(function (Game $game): array {
                $score = $game->scores->first();

                return [
                    'slug' => $game->slug,
                    'name' => $game->name,
                    'category' => $game->category,
                    'description' => $game->description,
                    'icon' => $game->icon,
                    'max_score' => $game->max_score,
                    'best_score' => $score?->best_score ?? 0,
                    'attempts' => $score?->attempts ?? 0,
                    'completed' => ($score?->completed_count ?? 0) > 0,
                ];
            });

        $totalScore = (int) $user->gameScores()->sum('best_score');
        $totals = GameScore::query()
            ->select('user_id')
            ->selectRaw('SUM(best_score) as total_score')
            ->groupBy('user_id');
        $position = DB::query()->fromSub($totals, 'ranked_scores')->where('total_score', '>', $totalScore)->count() + 1;

        return [
            'games' => $games,
            'progress' => [
                'completed' => $games->where('completed', true)->count(),
                'total' => $games->count(),
                'percentage' => $games->count() > 0 ? (int) round($games->where('completed', true)->count() / $games->count() * 100) : 0,
            ],
            'stats' => [
                'points' => $totalScore,
                'position' => $position,
                'attempts' => (int) $user->gameScores()->sum('attempts'),
            ],
            'achievements' => $user->achievements()->orderByPivot('earned_at', 'desc')->get()->map->only(['slug', 'name', 'description', 'icon', 'points']),
            'campus_tour_completed' => $user->campus_tour_completed_at !== null,
        ];
    }
}
