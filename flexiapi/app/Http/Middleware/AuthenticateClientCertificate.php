<?php

namespace App\Http\Middleware;

use App\Account;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientCertificate
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->server('SSL_CLIENT_CERT')) {
            if (!space()->client_certificate_authentication) {
                abort(403, 'Not a valid authentication method');
            }

            $cn = $request->server('SSL_CLIENT_S_DN');

            if (!$cn || !Str::startsWith($cn, 'CN=')) {
                abort(403, 'The client certificate does not contain a valid CN.');
            }

            $sip = Str::chopStart($cn, 'CN=');
            $sip = parseSIP($sip);

            if ($sip == null) {
                abort(403, 'The certificate CN is not a valid SIP address.');
            }

            $account = Account::where('domain', $sip[1])
                ->where('username', $sip[0])
                ->first();

            if (!$account) {
                abort(403, 'The certificate is not linked to any account in the system.');
            }

            Auth::login($account);
        }

        return $next($request);
    }
}
