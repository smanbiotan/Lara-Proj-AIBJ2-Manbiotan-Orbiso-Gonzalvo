<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Only authenticated administrators (admin guard) may continue. Customers are redirected. */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Administrator authentication required.'], 401);
            }
            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}
