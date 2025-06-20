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
use App\ApiKey;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Closure;
use Validator;

class AuthenticateDigestOrKey
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() && Auth::check()) {
            return $next($request);
        }

        // Key authentication

        if ($request->header('x-api-key') || $request->cookie('x-api-key')) {
            $apiKey = ApiKey::with(['account' => function ($query) {
                $query->withoutGlobalScopes();
            }])->where('key', $request->header('x-api-key') ?? $request->cookie('x-api-key'))->first();

            if ($apiKey && ($apiKey->ip == null || $apiKey->ip == $request->ip())) {
                $apiKey->last_used_at = Carbon::now();
                $apiKey->requests = $apiKey->requests + 1;
                $apiKey->save();

                Auth::login($apiKey->account);
                $response = $next($request);

                return $response;
            }

            return $this->generateUnauthorizedResponse(null, 'Invalid API Key');
        }

        if (empty($request->header('From'))) {
            return $this->generateUnauthorizedResponse(null, 'From header is required or invalid token');
        }

        $from = $this->extractFromHeader($request->header('From'));
        list($username, $domain) = parseSIP($from);

        $account = Account::withoutGlobalScopes()
                          ->where('username', $username)
                          ->where('domain', $domain)
                          ->firstOrFail();

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
            Validator::make($auth, [
                'opaque'    => 'required|in:'.$this->getOpaque(),
                //'uri'       => 'in:/'.$request->path(),
                'qop'       => 'required|in:auth',
                'realm'     => 'required|in:'.$account->resolvedRealm,
                'nc'        => 'required',
                'cnonce'    => 'required',
                'algorithm' => [
                    'required',
                    Rule::in(array_keys(passwordAlgorithms())),
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
                                    ->first();
            }

            if (!$password) {
                return $this->generateUnauthorizedResponse($account, 'Wrong algorithm');
            }

            $hash = passwordAlgorithms()[$auth['algorithm']];

            // Hashing and checking
            $a1 = $password->algorithm == 'CLRTXT'
                ? hash($hash, $account->username.':'.$account->resolvedRealm.':'.$password->password)
                : $password->password; // username:realm/domain:password
            $a2 = hash($hash, $request->method().':'.$auth['uri']);

            $validResponse = hash(
                $hash,
                $a1.
                ':'.$auth['nonce'].
                ':'.$auth['nc'].
                ':'.$auth['cnonce'].
                ':'.$auth['qop'].
                ':'.$a2
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

    private function generateUnauthorizedResponse(?Account $account = null, $message = 'Unauthenticated request')
    {
        $response = new Response();

        if ($account) {
            $nonce = generateValidNonce($account);
            $headers = $this->generateAuthHeaders($account, $nonce);

            if (!empty($headers)) {
                $response->header('WWW-Authenticate', $headers);
            }
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

        foreach ($account->passwords as $password) {
            if ($password->algorithm == 'CLRTXT') {
                foreach (array_keys(passwordAlgorithms()) as $algorithm) {
                    array_push(
                        $headers,
                        $this->generateAuthHeader($account->resolvedRealm, $algorithm, $nonce)
                    );
                }
                break;
            } elseif (\in_array($password->algorithm, array_keys(passwordAlgorithms()))) {
                array_push(
                    $headers,
                    $this->generateAuthHeader($account->resolvedRealm, $password->algorithm, $nonce)
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
        list($from) = explode(';', $string);
        return \rawurldecode($from);
    }

    private function getOpaque(): string
    {
        return base64_encode(config('app.key'));
    }
}
