<?php

namespace App\Models;

use Database\Factories\GameScoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'game_id', 'best_score', 'best_duration_ms', 'attempts', 'completed_count', 'achieved_at'])]
class GameScore extends Model
{
    /** @use HasFactory<GameScoreFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'best_score' => 'integer',
            'best_duration_ms' => 'integer',
            'attempts' => 'integer',
            'completed_count' => 'integer',
            'achieved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
