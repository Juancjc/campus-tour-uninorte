<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\GameScore;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        $summary = DB::transaction(function () use ($request): array {
            $sessionsCancelled = GameSession::query()
                ->where('status', 'started')
                ->update([
                    'status' => 'cancelled',
                    'result' => 'ranking_reset',
                    'completed_at' => now(),
                ]);

            $scoresDeleted = GameScore::query()->delete();

            $usersReset = User::query()
                ->where(function (Builder $query): void {
                    $query->where('points', '>', 0)
                        ->orWhereNotNull('campus_tour_completed_at');
                })
                ->update([
                    'points' => 0,
                    'campus_tour_completed_at' => null,
                ]);

            $summary = [
                'scores_deleted' => $scoresDeleted,
                'sessions_cancelled' => $sessionsCancelled,
                'users_reset' => $usersReset,
            ];

            AdminAuditLog::query()->create([
                'admin_id' => $request->user()->id,
                'action' => 'reset_ranking',
                'entity' => 'ranking',
                'ip_address' => $request->ip(),
                'metadata' => $summary,
            ]);

            return $summary;
        }, attempts: 3);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Ranking zerado: {$summary['scores_deleted']} placares removidos e {$summary['users_reset']} usuários atualizados.");
    }
}
