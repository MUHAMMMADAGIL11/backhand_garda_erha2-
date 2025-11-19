<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtCookieMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
{
    if (!$request->bearerToken()) {
        $token = $request->cookie('token');
        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
            \Log::debug('Token added to header: ' . $token); // Verifikasi token yang diteruskan
        }
    }
    \Log::debug('Authorization header: ' . $request->header('Authorization'));
    return $next($request);
}



}
