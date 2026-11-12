<?php

namespace App\Http\Middleware;

use App\Account;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientCertificate
{
    public function handle(Request $request, Closure $next): Response
    {
        $pem = $request->server('SSL_CLIENT_CERT');

        if ($pem) {
            if (!space()->client_certificate_authentication) {
                abort(403, 'Not a valid authentication method');
            }

            $certificate = openssl_x509_parse($pem);

            if ($certificate === false) {
                abort(403, 'The client certificate cannot be parsed.');
            }

            $identities = $this->extractIdentities($certificate);

            if (empty($identities)) {
                abort(403, 'The client certificate does not contain a SIP address in its subjectAltName or CN.');
            }

            $account = null;

            foreach ($identities as $identity) {
                $sip = parseSIP($identity);

                if ($sip == null) {
                    continue;
                }

                $account = Account::where('domain', $sip[1])
                    ->where('username', $sip[0])
                    ->first();

                if ($account) {
                    break;
                }
            }

            if (!$account) {
                abort(403, 'The certificate is not linked to any account in the system.');
            }

            Auth::login($account);
        }

        return $next($request);
    }

    private function extractIdentities(array $certificate): array
    {
        $candidates = [];

        $san = $certificate['extensions']['subjectAltName'] ?? '';

        if (preg_match_all('/(?:^|,)\s*URI:([^,\s]+)/i', $san, $matches)) {
            $candidates = $matches[1];
        }

        foreach ((array)($certificate['subject']['CN'] ?? []) as $cn) {
            $candidates[] = $cn;
        }

        return array_values(array_unique(array_filter(
            $candidates,
            fn ($value) => is_string($value) && stripos($value, 'sip:') === 0
        )));
    }
}
