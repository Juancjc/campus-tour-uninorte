<?php

namespace App\Models;

use Database\Factories\GameSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['uuid', 'user_id', 'game_id', 'status', 'result', 'score', 'duration_ms', 'attempts', 'level', 'metadata', 'started_at', 'completed_at'])]
class GameSession extends Model
{
    /** @use HasFactory<GameSessionFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (GameSession $session): void {
            $session->uuid ??= (string) Str::uuid();
            $session->started_at ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'score' => 'integer',
            'duration_ms' => 'integer',
            'attempts' => 'integer',
            'level' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function events(): HasMany
    {
        return $this->hasMany(GameEvent::class);
    }
}
