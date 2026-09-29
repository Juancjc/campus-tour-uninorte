<?php

namespace App\Http\Middleware;

use App\Models\AccessLog;
use App\Support\UserAgentParser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordAccess
{
    public function __construct(private UserAgentParser $parser) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);
        $visitorUuid = $this->validUuid($request->cookie('campus_visitor'))
            ? $request->cookie('campus_visitor')
            : (string) Str::uuid();

        $response = $next($request);

        if ($this->shouldRecord($request)) {
            try {
                $agent = $this->parser->parse($request->userAgent());
                AccessLog::query()->create([
                    'user_id' => $request->user()?->id,
                    'visitor_uuid' => $visitorUuid,
                    'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                    'ip_address' => $request->ip(),
                    'method' => $request->method(),
                    'url' => $request->url(),
                    'route_name' => $request->route()?->getName(),
                    'query_string' => $this->sanitizedQuery($request),
                    'referer' => $request->headers->get('referer'),
                    'user_agent' => $request->userAgent(),
                    ...$agent,
                    'language' => $request->getPreferredLanguage(),
                    'status_code' => $response->getStatusCode(),
                    'duration_ms' => (int) round((hrtime(true) - $startedAt) / 1_000_000),
                    'is_authenticated' => $request->user() !== null,
                ]);
            } catch (Throwable $exception) {
                Log::warning('Não foi possível registrar o acesso.', ['exception' => $exception]);
            }
        }

        if (! $request->cookies->has('campus_visitor')) {
            $response->headers->setCookie(cookie(
                'campus_visitor',
                $visitorUuid,
                60 * 24 * 365,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax',
            ));
        }

        return $response;
    }

    private function shouldRecord(Request $request): bool
    {
        return ! $request->is('build/*', 'storage/*', 'favicon.ico', 'robots.txt', 'health', 'up');
    }

    private function validUuid(?string $value): bool
    {
        return is_string($value) && Str::isUuid($value);
    }

    private function sanitizedQuery(Request $request): ?string
    {
        $query = $request->query();
        foreach (['password', 'token', 'api_key', 'key', 'signature'] as $sensitiveKey) {
            if (array_key_exists($sensitiveKey, $query)) {
                $query[$sensitiveKey] = '[redacted]';
            }
        }

        return $query === [] ? null : http_build_query($query);
    }
}
