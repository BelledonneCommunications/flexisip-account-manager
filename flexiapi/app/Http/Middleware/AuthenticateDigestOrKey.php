<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace App\Http\Middleware;

use App\Account;
use App\Helpers\Utils;

use Fabiang\Sasl\Sasl;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Closure;
use Validator;

class AuthenticateDigestOrKey
{
    const ALGORITHMS = [
        'MD5'     => 'md5',
        'SHA-256' => 'sha256',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $validator = Validator::make(['from' => $request->header('From')], [
            'from'    => 'required',
        ])->validate();

        $from = $this->extractFromHeader($request->header('From'));
        list($username, $domain) = explode('@', $from);

        $account = Account::where('username', $username)
                          ->where('domain', $domain)
                          ->firstOrFail();

        $resolvedRealm = config('app.realm') ?? $domain;

        // Check if activated
        if (!$account->activated) {
            return $this->generateUnauthorizedResponse($account);
        }

        // Key authentication
        if ($request->header('x-api-key')) {
            if ($account->apiKey
            && $account->apiKey->key == $request->header('x-api-key')) {
                Auth::login($account);
                $response = $next($request);

                return $response;
            }

            return $this->generateUnauthorizedResponse($account);
        }

        // DIGEST authentication

        if ($request->header('Authorization')) {
            $auth = $this->extractAuthorizationHeader($request->header('Authorization'));
            $storedNonce = $account->nonces()->where('nonce', $auth['nonce'])->first();

            // Nonce handling
            if ($storedNonce && (int)$storedNonce->nc >= (int)\hexdec($auth['nc'])) {
                $storedNonce->delete();

                return $this->generateUnauthorizedResponse($account, 'Nonce replayed');
            } elseif (!$storedNonce) {
                return $this->generateUnauthorizedResponse($account, 'Nonce invalid');
            }

            $storedNonce->nc++;
            $storedNonce->save();

            // Validation
            $validator = Validator::make($auth, [
                'opaque'    => 'required|in:'.$this->getOpaque(),
                //'uri'       => 'in:/'.$request->path(),
                'qop'       => 'required|in:auth',
                'realm'     => 'required|in:'.$resolvedRealm,
                'nc'        => 'required',
                'cnonce'    => 'required',
                'algorithm' => [
                    'required',
                    Rule::in(array_keys(self::ALGORITHMS)),
                ],
                'username'  => 'required|in:'.$username,
            ])->validate();

            // Headers
            $headers = $this->generateAuthHeaders($account, $storedNonce->nonce);

            // Retrieving the user and related passwords
            $password = $account->passwords()
                                ->where('algorithm', $auth['algorithm'])
                                ->first();

            // CLRTXT case
            if (!$password) {
                $password = $account->passwords()
                                    ->where('algorithm', 'CLRTXT')
                                    ->firstOrFail();
            }

            $hash = self::ALGORITHMS[$auth['algorithm']];

            // Hashing and checking
            $A1 = $password->algorithm == 'CLRTXT'
                ? hash($hash, $account->username.':'.$resolvedRealm.':'.$password->password)
                : $password->password; // username:realm/domain:password
            $A2 = hash($hash, $request->method().':'.$auth['uri']);

            $validResponse = hash($hash,
                $A1.
                ':'.$auth['nonce'].
                ':'.$auth['nc'].
                ':'.$auth['cnonce'].
                ':'.$auth['qop'].
                ':'.$A2
            );

            // Auth response don't match
            if (!hash_equals($auth['response'], $validResponse)) {
                return $this->generateUnauthorizedResponse($account, 'Unauthorized');
            }

            Auth::login($account);
            $response = $next($request);

            if (!empty($headers)) {
                $response->header('WWW-Authenticate', $headers);
            }

            return $response;
        }

        return $this->generateUnauthorizedResponse($account);
    }

    private function generateUnauthorizedResponse(Account $account, $message = 'Unauthenticated request')
    {
        $response = new Response;

        $nonce = Utils::generateValidNonce($account);
        $headers = $this->generateAuthHeaders($account, $nonce);

        if (!empty($headers)) {
            $response->header('WWW-Authenticate', $headers);
        }

        $response->setStatusCode(401);
        $response->setContent($message);

        return $response;
    }

    private function extractAuthorizationHeader(string $string): array
    {
        preg_match_all(
            '@(realm|username|nonce|uri|nc|cnonce|qop|response|opaque|algorithm)=[\'"]?([^\'",]+)@',
            $string,
            $array
        );

        $array = array_combine($array[1], $array[2]);

        if (!array_key_exists('algorithm', $array)) {
            $array['algorithm'] = 'MD5';
        }

        return $array;
    }

    private function generateAuthHeaders(Account $account, string $nonce): array
    {
        $headers = [];
        $resolvedRealm = config('app.realm') ?? $account->domain;

        foreach ($account->passwords as $password) {
            if ($password->algorithm == 'CLRTXT') {
                foreach (array_keys(self::ALGORITHMS) as $algorithm) {
                    array_push(
                        $headers,
                        $this->generateAuthHeader($resolvedRealm, $algorithm, $nonce)
                    );
                }
                break;
            } else if (\in_array($password->algorithm, array_keys(self::ALGORITHMS))) {
                array_push(
                    $headers,
                    $this->generateAuthHeader($resolvedRealm, $password->algorithm, $nonce)
                );
            }
        }

        return $headers;
    }

    private function generateAuthHeader(string $realm, string $algorithm, string $nonce): string
    {
        return 'Digest realm="'.$realm.'",qop="auth",algorithm='.$algorithm.',nonce="'.$nonce.'",opaque="'.$this->getOpaque().'"';
    }

    private function extractFromHeader(string $string): string
    {
        list($from) = explode(';', \substr($string, 4));
        return \rawurldecode($from);
    }

    private function getOpaque(): string
    {
        return base64_encode(env('APP_KEY'));
    }
}
