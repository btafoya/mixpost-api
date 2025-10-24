<?php

namespace Inovector\MixpostApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceHttps
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('mixpost-api.security.https_only', false)) {
            return $next($request);
        }

        if (! $request->secure() && app()->environment('production')) {
            return response()->json([
                'success' => false,
                'message' => 'HTTPS is required for API requests',
            ], 426); // 426 Upgrade Required
        }

        return $next($request);
    }
}
