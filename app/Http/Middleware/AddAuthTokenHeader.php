<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddAuthTokenHeader
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $cookieName = env('AUTH_COOKIE_NAME');

        if (!$request->bearerToken()) {
            if ($request->hasCookie($cookieName)) {
                $token = $request->cookie($cookieName);

                $request->headers->add([
                    'Authorization' => 'Bearer ' . $token
                ]);
            }
        }

        return $next($request);
    }
}
