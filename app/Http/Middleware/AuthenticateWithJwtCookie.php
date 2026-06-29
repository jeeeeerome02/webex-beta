<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithJwtCookie
{
    /**
     * Inject the JWT stored in the HttpOnly cookie into the Authorization
     * header so the JWT guard can authenticate the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('access_token');

        if ($token && ! $request->headers->has('Authorization')) {
            $request->headers->set('Authorization', 'Bearer '.$token);
        }

        return $next($request);
    }
}
