<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSpaceSSO
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->space || !$request->space->ssoServer) {
            abort(403, 'SSO is not enabled on this space');
        }

        return $next($request);
    }
}
