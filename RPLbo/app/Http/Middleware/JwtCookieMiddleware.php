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
        // Jika tidak ada Bearer Token di header, cek cookie
        if (!$request->bearerToken()) {
            $token = $request->cookie('access_token');
            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }

        return $next($request);
    }
}
