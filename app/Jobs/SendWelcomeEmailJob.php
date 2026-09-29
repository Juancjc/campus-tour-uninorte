<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MailApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 180];

    public function __construct(public int $userId) {}

    /**
     * Execute the job.
     */
    public function handle(MailApiService $mailApi): void
    {
        $user = User::query()->findOrFail($this->userId);
        $html = view('emails.welcome', [
            'user' => $user,
            'appUrl' => config('app.url'),
        ])->render();

        $mailApi->send(
            $user->email,
            'Bem-vindo ao Campus Tour UniNorte 2026!',
            $html,
        );
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Falha definitiva no envio do e-mail de boas-vindas.', [
            'user_id' => $this->userId,
            'exception' => $exception,
        ]);
    }
}
