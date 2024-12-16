<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class IsSpaceExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(config('app.root_host'))) {
            return abort(503, 'APP_ROOT_HOST is not configured');
        }

        $space = \App\Space::where('host', $request->header('host'))->first();

        if ($space) {
            if (!str_ends_with($space->host, config('app.root_host'))) {
                return abort(503, 'The APP_ROOT_HOST configured does not match with the current root domain');
            }

            Config::set('app.url', '://' . $space->host);
            Config::set('app.sip_domain', $space->domain);

            if ($request->user() && !$request->user()->superAdmin && $space?->isExpired()) {
                abort(403, 'The related Space has expired');
            }

            return $next($request);
        }

        return abort(404, 'Host not configured');
    }
}
