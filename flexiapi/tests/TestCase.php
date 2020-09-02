<?php

namespace Tests;

use App\Password;
use App\Helpers\Utils;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    const ALGORITHMS = ['md5' => 'MD5', 'sha256' => 'SHA-256'];

    protected function generateDigest(Password $password, $response, $hash = 'md5', $nc = '00000001')
    {
        $challenge = \substr($response->headers->get('www-authenticate'), 7);
        $extractedChallenge = $this->extractAuthenticateHeader($challenge);

        $cnonce = Utils::generateNonce();

        $A1 = $password->password;
        $A2 = hash($hash, $this->method . ':' . $this->route);
        $response = hash($hash,
            sprintf(
                '%s:%s:%s:%s:%s:%s',
                $A1,
                $extractedChallenge['nonce'],
                $nc,
                $cnonce,
                $extractedChallenge['qop'],
                $A2
            )
        );

        $digest = \sprintf(
            'username="%s",realm="%s",nonce="%s",nc=%s,cnonce="%s",uri="%s",qop=%s,response="%s",opaque="%s",algorithm=%s',
            $password->account->identifier,
            $extractedChallenge['realm'],
            $extractedChallenge['nonce'],
            $nc,
            $cnonce,
            $this->route,
            $extractedChallenge['qop'],
            $response,
            $extractedChallenge['opaque'],
            self::ALGORITHMS[$hash],
        );

        return 'Digest ' . $digest;
    }

    protected function extractAuthenticateHeader(string $string): array
    {
        preg_match_all(
            '@(realm|nonce|qop|opaque|algorithm)=[\'"]?([^\'",]+)@',
            $string,
            $array
        );

        return array_combine($array[1], $array[2]);
    }
}
