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
use App\Opaque;
use App\PasswordAlgorithm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Validation\Rules\Enum;
use Validator;

class AuthenticateDigest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('x-api-key') || ($request->server('SSL_CLIENT_CERT') || $request->bearerToken()) && Auth::check()) {
            return $next($request);
        }

        if (empty($request->header('From'))) {
            return $this->generateUnauthorizedResponse($request, message: 'From header is required or invalid token');
        }

        $from = $this->extractFromHeader($request->header('From'));

        $sip = parseSIP($from);

        if ($sip == null) {
            return $this->generateUnauthorizedResponse($request, message: 'Invalid SIP address');
        }

        list($username, $domain) = $sip;

        $account = Account::withoutGlobalScopes()
            ->where('username', $username)
            ->where('domain', $domain)
            ->firstOrFail();

        // DIGEST authentication

        if ($request->header('Authorization')) {
            $auth = $this->extractAuthorizationHeader($request->header('Authorization'));
            $storedOpaque = $account->opaques()
                ->where('opaque', $auth['opaque'])
                ->where('ip', $request->ip())
                ->first();

            // Nonce handling
            if ($storedOpaque && (int) $storedOpaque->nc >= (int) \hexdec($auth['nc'])) {
                $storedOpaque->delete();

                return $this->generateUnauthorizedResponse($request, $account, 'Nonce replayed');
            } elseif (!$storedOpaque) {
                return $this->generateUnauthorizedResponse($request, $account, 'Invalid opaque');
            }

            $storedOpaque->nc++;
            $storedOpaque->save();

            // Validation
            Validator::make($auth, [
                'opaque' => 'required|in:' . $storedOpaque->opaque,
                'uri' => 'in:/' . $request->path(),
                'qop' => 'required|in:auth',
                'realm' => 'required|in:' . $account->resolvedRealm,
                'nc' => 'required',
                'cnonce' => 'required',
                'algorithm' => [
                    'required',
                    new Enum(PasswordAlgorithm::class),
                ],
                'username' => 'required|in:' . $username,
            ])->validate();

            // Headers
            $headers = $this->generateAuthHeaders($account, $storedOpaque);

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
                return $this->generateUnauthorizedResponse($request, $account, 'Wrong algorithm');
            }

            $hash = PasswordAlgorithm::from($auth['algorithm'])->hashFunction();

            // Hashing and checking
            $a1 = $password->algorithm == 'CLRTXT'
                ? hash($hash, $account->username . ':' . $account->resolvedRealm . ':' . $password->password)
                : $password->password; // username:realm/domain:password
            $a2 = hash($hash, $request->method() . ':' . $auth['uri']);

            $validResponse = hash(
                $hash,
                $a1 .
                ':' . $auth['nonce'] .
                ':' . $auth['nc'] .
                ':' . $auth['cnonce'] .
                ':' . $auth['qop'] .
                ':' . $a2
            );

            // Auth response don't match
            if (!hash_equals($auth['response'], $validResponse)) {
                return $this->generateUnauthorizedResponse($request, $account, 'Unauthorized');
            }

            Auth::login($account);
            $response = $next($request);

            if (!empty($headers)) {
                $response->header('WWW-Authenticate', $headers);
            }

            return $response;
        }

        return $this->generateUnauthorizedResponse($request, $account);
    }

    private function generateUnauthorizedResponse(Request $request, ?Account $account = null, ?string $message = 'Unauthenticated request')
    {
        $response = new Response;

        if ($account) {
            $opaque = $this->generateOpaque($request, $account);
            $headers = $this->generateAuthHeaders($account, $opaque);

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

    private function generateAuthHeaders(Account $account, Opaque $opaque): array
    {
        $headers = [];

        foreach ($account->passwords as $password) {
            if ($password->algorithm == 'CLRTXT') {
                foreach (PasswordAlgorithm::cases() as $algorithm) {
                    array_push(
                        $headers,
                        $this->generateAuthHeader($account->resolvedRealm, $algorithm->value, $opaque)
                    );
                }
                break;
            } elseif (\in_array($password->algorithm, array_column(PasswordAlgorithm::cases(), 'value'))) {
                array_push(
                    $headers,
                    $this->generateAuthHeader($account->resolvedRealm, $password->algorithm, $opaque)
                );
            }
        }

        return $headers;
    }

    private function generateOpaque(Request $request, Account $account): Opaque
    {
        $opaque = new Opaque;
        $opaque->opaque = generateNonce();
        $opaque->account_id = $account->id;
        $opaque->ip = $request->ip();
        $opaque->nonce = generateNonce();
        $opaque->save();

        return $opaque;
    }

    private function generateAuthHeader(string $realm, string $algorithm, Opaque $opaque): string
    {
        return 'Digest realm="' . $realm . '",qop="auth",algorithm=' . $algorithm . ',nonce="' . $opaque->nonce . '",opaque="' . $opaque->opaque . '"';
    }

    private function extractFromHeader(string $string): string
    {
        list($from) = explode(';', $string);
        return \rawurldecode($from);
    }
}
