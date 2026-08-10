<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSpaceOIDC
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->space || !$request->space->oidcAuthenticationConfiguration) {
            abort(403, 'OIDC is not enabled on this space');
        }

        return $next($request);
    }
}
