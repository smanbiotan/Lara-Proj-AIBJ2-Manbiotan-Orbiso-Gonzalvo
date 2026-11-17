<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects /api/n8n/* tool endpoints. n8n must send:  Authorization: Bearer <N8N_API_TOKEN>
 * If no token is configured, the endpoints stay closed.
 */
class VerifyN8nToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('salon.n8n.api_token');
        $given = (string) $request->bearerToken();

        if ($expected === '' || strlen($expected) < 16 || !hash_equals($expected, $given)) {
            return response()->json(['message' => 'Unauthorized automation request.'], 401);
        }

        return $next($request);
    }
}
