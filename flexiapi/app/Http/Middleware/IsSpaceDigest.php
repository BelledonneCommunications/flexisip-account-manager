<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSpaceDigest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->space || !$request->space->digestAuthenticationConfiguration) {
            abort(403, 'Digest is not enabled on this space');
        }

        return $next($request);
    }
}
