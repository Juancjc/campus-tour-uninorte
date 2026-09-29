<?php

namespace App\Services;

use App\Models\AccessLog;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    /** @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function dashboard(array $filters = []): array
    {
        $accessQuery = $this->applyPeriod(AccessLog::query(), $filters);
        $userQuery = $this->applyPeriod(User::query(), $filters);
        $sessionQuery = $this->applyPeriod(GameSession::query(), $filters);

        return [
            'metrics' => [
                'users' => (clone $userQuery)->where('is_admin', false)->count(),
                'visitors' => (clone $accessQuery)->whereNotNull('visitor_uuid')->distinct('visitor_uuid')->count('visitor_uuid'),
                'accesses' => (clone $accessQuery)->count(),
                'players' => (clone $sessionQuery)->distinct('user_id')->count('user_id'),
                'sessions_started' => (clone $sessionQuery)->count(),
                'sessions_completed' => (clone $sessionQuery)->where('status', 'completed')->count(),
            ],
            'accesses_by_hour' => (clone $accessQuery)
                ->get(['created_at'])
                ->groupBy(fn (AccessLog $log): string => $log->created_at->format('H'))
                ->map(fn (Collection $logs, string $hour): array => ['label' => $hour.'h', 'value' => $logs->count()])
                ->sortKeys()
                ->values(),
            'games' => Game::query()->where('active', true)->withCount(['sessions' => fn (Builder $query) => $this->applyPeriod($query, $filters)])->orderByDesc('sessions_count')->get()->map(fn (Game $game): array => ['label' => $game->name, 'value' => $game->sessions_count]),
            'devices' => $this->breakdown((clone $accessQuery), 'device_type'),
            'browsers' => $this->breakdown((clone $accessQuery), 'browser'),
            'systems' => $this->breakdown((clone $accessQuery), 'operating_system'),
        ];
    }

    /** @param array<string, mixed> $filters
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    public function reports(array $filters = []): array
    {
        $users = $this->applyPeriod(User::query()->where('is_admin', false), $filters);
        if (! empty($filters['school'])) {
            $users->where('school_name', 'like', '%'.$filters['school'].'%');
        }
        if (! empty($filters['profession_id'])) {
            $users->where('profession_id', $filters['profession_id']);
        }

        $schools = (clone $users)
            ->select('school_name')
            ->selectRaw('COUNT(*) as students')
            ->whereNotNull('school_name')
            ->groupBy('school_name')
            ->orderByDesc('students')
            ->limit(100)
            ->get()
            ->map(fn ($row): array => ['name' => $row->school_name, 'students' => (int) $row->students]);

        $professions = (clone $users)
            ->join('professions', 'professions.id', '=', 'users.profession_id')
            ->select('professions.name', 'professions.category')
            ->selectRaw('COUNT(*) as students')
            ->groupBy('professions.id', 'professions.name', 'professions.category')
            ->orderByDesc('students')
            ->get()
            ->map(fn ($row): array => ['name' => $row->name, 'category' => $row->category, 'students' => (int) $row->students]);

        $games = Game::query()
            ->withCount([
                'sessions as sessions_count' => fn (Builder $query) => $this->applyPeriod($query, $filters),
                'sessions as completions_count' => fn (Builder $query) => $this->applyPeriod($query, $filters)->where('status', 'completed'),
            ])
            ->withAvg(['sessions as average_score' => fn (Builder $query) => $this->applyPeriod($query, $filters)->where('status', 'completed')], 'score')
            ->withAvg(['sessions as average_duration_ms' => fn (Builder $query) => $this->applyPeriod($query, $filters)->where('status', 'completed')], 'duration_ms')
            ->orderBy('display_order')
            ->get()
            ->map(fn (Game $game): array => [
                'name' => $game->name,
                'sessions' => $game->sessions_count,
                'completions' => $game->completions_count,
                'abandonments' => max(0, $game->sessions_count - $game->completions_count),
                'average_score' => (int) round($game->average_score ?? 0),
                'average_duration_ms' => (int) round($game->average_duration_ms ?? 0),
            ]);

        return compact('schools', 'professions', 'games');
    }

    /** @param Builder<*> $query
     * @param  array<string, mixed>  $filters
     * @return Builder<*>
     */
    private function applyPeriod(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['from'] ?? null, fn (Builder $builder, string $from): Builder => $builder->whereDate($builder->getModel()->qualifyColumn('created_at'), '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $builder, string $to): Builder => $builder->whereDate($builder->getModel()->qualifyColumn('created_at'), '<=', $to));
    }

    /** @param Builder<AccessLog> $query
     * @return Collection<int, array{label: string, value: int}>
     */
    private function breakdown(Builder $query, string $column): Collection
    {
        $allowed = ['device_type', 'browser', 'operating_system'];
        abort_unless(in_array($column, $allowed, true), 400);

        return $query->select($column)->selectRaw('COUNT(*) as total')->groupBy($column)->orderByDesc('total')->limit(10)->get()
            ->map(fn ($row): array => ['label' => $row->{$column} ?: 'Desconhecido', 'value' => (int) $row->total]);
    }
}
