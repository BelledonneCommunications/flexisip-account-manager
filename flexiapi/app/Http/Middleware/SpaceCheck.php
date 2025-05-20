<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SpaceCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(config('app.root_host'))) {
            return abort(503, 'APP_ROOT_HOST is not configured');
        }

        $space = space();

        if ($space) {
            if (!str_ends_with($space->host, config('app.root_host'))) {
                return abort(503, 'The APP_ROOT_HOST configured does not match with the current root domain');
            }

            Config::set('app.url', '://' . $space->host);
            Config::set('app.sip_domain', $space->domain);

            if ($request->user() && !$request->user()->superAdmin && $space?->isExpired()) {
                abort($request->expectsJson() ? 403 : 490, 'The related Space has expired');
            }

            // Custom email integration
            if ($space->emailServer) {
                $config = [
                    'driver'     => config('mail.driver'),
                    'encryption' => config('mail.encryption'),
                    'host'       => $space->emailServer->host,
                    'port'       => $space->emailServer->port,
                    'from'       => [
                        'address' => $space->emailServer->from_address,
                        'name' => $space->emailServer->from_name
                    ],
                    'username'   => $space->emailServer->username,
                    'password'   => $space->emailServer->password,
                    'signature'  => $space->emailServer->signature ?? config('mail.signature')
                ] + Config::get('mail');

                Config::set('mail', $config);
            }

            return $next($request);
        }

        return abort(404, 'Host not configured');
    }
}
