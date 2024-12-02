<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSpaceExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->superAdmin && $request->get('resolvedSpace')?->isExpired()) {
            abort(403, 'The related Space has expired');
        }

        return $next($request);
    }
}
