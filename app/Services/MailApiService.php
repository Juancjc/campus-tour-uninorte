<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MailApiService
{
    public function send(string $to, string $subject, string $html): Response
    {
        $baseUrl = rtrim((string) config('campus.mail_api.url'), '/');
        $apiKey = config('campus.mail_api.key');
        $apiToken = config('campus.mail_api.token');

        if (! $baseUrl || ! $apiKey || ! $apiToken) {
            throw new RuntimeException('A API de e-mail não está configurada.');
        }

        return Http::connectTimeout(config('campus.mail_api.connect_timeout'))
            ->timeout(config('campus.mail_api.timeout'))
            ->acceptJson()
            ->withHeaders([
                'x-api-key' => $apiKey,
                'x-api-token' => $apiToken,
            ])
            ->post($baseUrl.'/mail/send', [
                'to' => $to,
                'subject' => $subject,
                'body' => $html,
            ])
            ->throw();
    }
}
