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
use Closure;
use DateTimeImmutable;
use Illuminate\Http\Request;
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

class AuthenticateJWT
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() && config('services.jwt.rsa_public_key_pem')) {
            if (!extension_loaded('sodium')) {
                abort(403, "PHP Sodium extension isn't loaded");
            }

            $publicKey = InMemory::plainText(config('services.jwt.rsa_public_key_pem'));
            $token = (new Parser(new JoseEncoder()))->parse($request->bearerToken());

            $signer = null;

            switch ($token->headers()->get('alg')) {
                case 'RS256':
                    $signer = new Sha256();
                    break;

                case 'RS384':
                    $signer = new Sha384();
                    break;

                case 'RS512':
                    $signer = new Sha512();
                    break;
            }

            if ($signer == null) {
                return $this->generateUnauthorizedBearerResponse('invalid_token', 'Unsupported RSA signature');
            }

            if (!(new Validator())->validate($token, new SignedWith($signer, $publicKey))) {
                return $this->generateUnauthorizedBearerResponse('invalid_token', 'Invalid JWT token signature');
            }

            if ($token->isExpired(new DateTimeImmutable())) {
                return $this->generateUnauthorizedBearerResponse('invalid_token', 'Expired JWT token');
            }

            $account = null;

            if ($token->claims()->has(config('services.jwt.sip_identifier'))) {
                list($username, $domain) = parseSIP($token->claims()->get(config('services.jwt.sip_identifier')));

                $account = Account::withoutGlobalScopes()
                                  ->where('username', $username)
                                  ->where('domain', $domain)
                                  ->first();
            } elseif ($token->claims()->has('email')) {
                $account = Account::withoutGlobalScopes()
                                  ->where('email', $token->claims()->get('email'))
                                  ->first();
            }

            if (!$account) {
                abort(403, 'The JWT token is not related to someone in the system');
            }

            Auth::login($account);

            return $next($request);
        }

        if (!empty(config('app.account_authentication_bearer'))) {
            $response = new Response();

            $response->header(
                'WWW-Authenticate',
                'Bearer ' . config('app.account_authentication_bearer')
            );

            $response->setStatusCode(401);

            return $response;
        }

        return $next($request);
    }

    private function generateUnauthorizedBearerResponse(string $error, string $description): Response
    {
        $bearer = 'Bearer ' . config('app.account_authentication_bearer');
        $bearer .= !empty(config('app.account_authentication_bearer'))
            ? ', '
            : '';

        $response = new Response();
        $response->header(
            'WWW-Authenticate',
            $bearer . 'error="' . $error . '", error_description="'. $description . '"'
        );
        $response->setStatusCode(401);

        return $response;
    }
}
