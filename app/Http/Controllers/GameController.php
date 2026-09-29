<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\GameCatalog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function show(Request $request, Game $game, GameCatalog $catalog): Response
    {
        abort_unless($game->active, 404);
        $score = $request->user()->gameScores()->whereBelongsTo($game)->first();

        return Inertia::render('Games/Show', [
            'game' => $game->only(['id', 'slug', 'name', 'category', 'description', 'icon', 'max_score']),
            'config' => $catalog->publicConfig($game),
            'bestScore' => $score?->best_score ?? 0,
        ]);
    }
}
