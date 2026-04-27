<?php

namespace App\Http\Middleware;

use Closure;
use App\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SpaceCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(config('app.root_host'))) {
            abort(503, 'APP_ROOT_HOST is not configured');
        }

        $space = Space::where('host', config('app.sip_domain') ?? request()->host())->first();

        if ($space != null) {
            if (!str_ends_with($space->host, config('app.root_host'))) {
                abort(503, 'The APP_ROOT_HOST configured does not match with the current root domain');
            }

            $request->merge(['space' => $space]);

            Config::set('app.url', '://' . $space->host);

            if ($space->isExpired()) {
                abort($request->expectsJson() ? 403 : 490, 'The related Space has expired');
            }

            $space->injectCustomEmailConfig();
            $space->injectKeycloakConfig();

            return $next($request);
        }

        abort(404, 'Host not configured');
    }
}
