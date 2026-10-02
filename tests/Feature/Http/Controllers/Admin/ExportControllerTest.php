<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_export_school_report_as_csv(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['school_name' => 'Escola Campus Tour']);

        $response = $this->actingAs($admin)->get(route('admin.reports.csv', ['type' => 'schools']));

        $response->assertOk()->assertDownload('campus-tour-schools.csv');
        $this->assertStringContainsString('Escola Campus Tour', $response->streamedContent());
        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $admin->id,
            'action' => 'exported_report',
            'entity' => 'schools',
        ]);
    }

    public function test_admin_can_export_school_report_as_xlsx(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['school_name' => 'Escola Campus Tour']);

        $response = $this->actingAs($admin)->get(route('admin.reports.xlsx', ['type' => 'schools']));

        $response->assertOk()->assertDownload('campus-tour-schools.xlsx');
        $this->assertStringStartsWith('PK', $response->streamedContent());
        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $admin->id,
            'action' => 'exported_report',
            'entity' => 'schools',
        ]);
    }

    public function test_regular_user_cannot_export_reports(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.reports.csv', ['type' => 'schools']));

        $response->assertForbidden();
    }

    public function test_game_export_excludes_inactive_games(): void
    {
        $admin = User::factory()->admin()->create();
        Game::factory()->create(['name' => 'Code Runner', 'active' => true]);
        Game::factory()->create(['name' => 'Rede em Ação', 'active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.reports.csv', ['type' => 'games']));

        $response->assertOk()->assertDownload('campus-tour-games.csv');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Code Runner', $content);
        $this->assertStringNotContainsString('Rede em Ação', $content);
    }
}
