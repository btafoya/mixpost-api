<?php

namespace Inovector\MixpostApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid API token.',
            ], 401);
        }

        // Check if token has expired
        $token = $request->user()->currentAccessToken();

        if ($token && $token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'API token has expired',
            ], 401);
        }

        // Check token abilities if needed
        if (config('mixpost-api.token.abilities_enabled', true)) {
            $requiredAbility = $request->route()->getName();

            if ($requiredAbility && !$request->user()->tokenCan('*') && !$request->user()->tokenCan($requiredAbility)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This token does not have the required permissions',
                ], 403);
            }
        }

        return $next($request);
    }
}
