<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use App\Services\MailApiService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendWelcomeEmailJobTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_job_sends_documented_mail_api_request(): void
    {
        config()->set('campus.mail_api.url', 'https://mail.example.test/api-nest-central-jc');
        config()->set('campus.mail_api.key', 'test-key');
        config()->set('campus.mail_api.token', 'test-token');
        $user = User::factory()->create(['name' => 'Ana <script>alert(1)</script>', 'email' => 'ana@example.com']);
        Http::fake([
            'https://mail.example.test/api-nest-central-jc/mail/send' => Http::response(['message' => 'E-mail enviado com sucesso']),
        ]);

        (new SendWelcomeEmailJob($user->id))->handle(app(MailApiService::class));

        Http::assertSent(function (Request $request): bool {
            $body = $request->data()['body'];

            return $request->url() === 'https://mail.example.test/api-nest-central-jc/mail/send'
                && $request->hasHeader('x-api-key', 'test-key')
                && $request->hasHeader('x-api-token', 'test-token')
                && $request['to'] === 'ana@example.com'
                && $request['subject'] === 'Bem-vindo ao Campus Tour UniNorte 2026!'
                && str_contains($body, '&lt;script&gt;')
                && ! str_contains($body, '<script>alert(1)</script>');
        });
    }
}
