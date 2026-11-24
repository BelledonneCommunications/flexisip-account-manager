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
use App\Space;
use Closure;
use DateTimeImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Illuminate\Auth\Middleware\Authenticate;

class AuthenticateJWT extends Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        if ($request->bearerToken() && $request->space->oidcAuthenticationConfiguration?->keys->isNotEmpty()) {
            if (!extension_loaded('sodium')) {
                abort(403, "PHP Sodium extension isn't loaded");
            }

            try {
                $token = (new Parser(new JoseEncoder))->parse($request->bearerToken());
            } catch (\Throwable $th) {
                return $this->generateUnauthorizedBearerResponse($request->space, 'invalid_token', 'Invalid bearer ' . $th->getMessage());
            }

            $signer = null;

            switch ($token->headers()->get('alg')) {
                case 'RS256':
                    $signer = new Sha256;
                    break;

                case 'RS384':
                    $signer = new Sha384;
                    break;

                case 'RS512':
                    $signer = new Sha512;
                    break;
            }

            if ($signer == null) {
                return $this->generateUnauthorizedBearerResponse($request->space, 'invalid_token', 'Unsupported RSA signature');
            }

            if ($token->isExpired(new DateTimeImmutable)) {
                return $this->generateUnauthorizedBearerResponse($request->space, 'invalid_token', 'Expired JWT token');
            }

            // Keys validation

            $checkKeys = false;
            $keys = $request->space->oidcAuthenticationConfiguration->keys;

            foreach ($keys as $key) {
                if ((new Validator)->validate($token, new SignedWith($signer, InMemory::plainText($key->public_key)))) {
                    $checkKeys = true;
                    break;
                }
            }

            if (!$checkKeys) {
                return $this->generateUnauthorizedBearerResponse($request->space, 'invalid_token', 'Invalid JWT token signature');
            }

            $account = null;

            if ($sipAddress = $token->claims()->get($request->space->oidcAuthenticationConfiguration->sip_id_claim)) {
                $sip = parseSIP($sipAddress);

                if ($sip == null) {
                    return $this->generateUnauthorizedBearerResponse($request->space, 'invalid_token', 'Invalid SIP address');
                }

                $account = Account::sip($sipAddress)->first();
            } elseif ($token->claims()->has('email')) {
                $account = Account::where('email', $token->claims()->get('email'))
                    ->first();
            }

            if (!$account) {
                return $this->generateUnauthorizedBearerResponse($request->space, 'invalid_token', 'The JWT token is not related to someone in the system');
            }

            Auth::login($account);

            return $next($request);
        }

        if ($request->space?->oidc_authentication_bearer) {
            $response = new Response;
            $response->header(
                'WWW-Authenticate',
                'Bearer ' . $request->space?->oidc_authentication_bearer
            );
            $response->setStatusCode(401);

            return $response;
        }

        return $next($request);
    }

    private function generateUnauthorizedBearerResponse(Space $space, string $error, string $description): Response
    {
        $bearer = 'Bearer ' . $space->oidc_authentication_bearer;
        $bearer .= $space->oidc_authentication_bearer != null
            ? ', '
            : '';

        $response = new Response;
        $response->header(
            'WWW-Authenticate',
            $bearer . 'error="' . $error . '", error_description="' . $description . '"'
        );
        $response->setStatusCode(401);

        return $response;
    }
}
