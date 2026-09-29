<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\AccessLog;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RecordAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_access_is_recorded_with_visitor_cookie_and_redacted_query(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/140.0.0.0 Safari/537.36',
            'Accept-Language' => 'pt-BR',
        ])->get('/?campaign=school&token=supersecret');

        $response->assertOk()->assertCookie('campus_visitor');
        $log = AccessLog::query()->sole();
        $this->assertSame('home', $log->route_name);
        $this->assertSame('Chrome', $log->browser);
        $this->assertSame('desktop', $log->device_type);
        $this->assertStringContainsString('campaign=school', $log->query_string);
        $this->assertStringNotContainsString('supersecret', $log->query_string);
        $this->assertFalse($log->is_authenticated);
    }

    public function test_healthcheck_does_not_pollute_access_logs(): void
    {
        $response = $this->get(route('health'));

        $response->assertOk()->assertExactJson(['status' => 'ok']);
        $this->assertDatabaseCount('access_logs', 0);
    }
}
