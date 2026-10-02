<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('games')
            ->where('slug', 'rede-em-acao')
            ->update([
                'active' => false,
                'updated_at' => now(),
            ]);

        $this->recalculateUserProgress();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('games')
            ->where('slug', 'rede-em-acao')
            ->update([
                'active' => true,
                'updated_at' => now(),
            ]);

        $this->recalculateUserProgress();
    }

    private function recalculateUserProgress(): void
    {
        $activeGameIds = DB::table('games')->where('active', true)->pluck('id');
        $activeGameCount = $activeGameIds->count();

        DB::table('users')
            ->select(['id', 'campus_tour_completed_at'])
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($activeGameIds, $activeGameCount): void {
                $scoresByUser = DB::table('game_scores')
                    ->whereIn('user_id', $users->pluck('id'))
                    ->whereIn('game_id', $activeGameIds)
                    ->select('user_id')
                    ->selectRaw('COALESCE(SUM(best_score), 0) as points')
                    ->selectRaw('SUM(CASE WHEN completed_count > 0 THEN 1 ELSE 0 END) as completed_games')
                    ->groupBy('user_id')
                    ->get()
                    ->keyBy('user_id');

                foreach ($users as $user) {
                    $scores = $scoresByUser->get($user->id);
                    $completedGames = (int) ($scores->completed_games ?? 0);
                    $completedTour = $activeGameCount > 0 && $completedGames >= $activeGameCount;

                    DB::table('users')->where('id', $user->id)->update([
                        'points' => (int) ($scores->points ?? 0),
                        'campus_tour_completed_at' => $completedTour
                            ? ($user->campus_tour_completed_at ?? now())
                            : null,
                        'updated_at' => now(),
                    ]);
                }
            });
    }
};
