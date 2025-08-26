<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCardDavCredentialsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->space->carddav_user_credentials) {
            return $next($request);
        }

        return abort(403, 'CardDav Credentials features disabled');
    }
}
