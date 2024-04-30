<?php

namespace App\Http\Middleware;

use App\Account;
use Closure;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                abort(403, "Your PHP setup doesn't have the Sodium extension loaded");
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
                abort(403, 'Unsupported RSA signature');
            }

            if (!(new Validator())->validate($token, new SignedWith($signer, $publicKey))) {
                abort(403, 'Invalid JWT token signature');
            }

            if ($token->isExpired(new DateTimeImmutable())) {
                abort(403, 'Expired JWT token');
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
        }

        return $next($request);
    }
}
