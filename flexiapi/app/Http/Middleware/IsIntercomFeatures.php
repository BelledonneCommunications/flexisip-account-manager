<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsIntercomFeatures
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->space->intercom_features) {
            return $next($request);
        }

        return abort(404, 'Intercom features disabled');
    }
}
