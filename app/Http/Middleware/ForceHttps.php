<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('campus.force_https') && ! $request->isSecure()) {
            if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
                abort(400, 'HTTPS é obrigatório.');
            }

            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
