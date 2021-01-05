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

namespace Tests;

use App\Password;
use App\Account;
use App\Helpers\Utils;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    const ALGORITHMS = ['md5' => 'MD5', 'sha256' => 'SHA-256'];

    protected function keyAuthenticated(Account $account)
    {
        return $this->withHeaders([
            'From' => 'sip:'.$account->identifier,
            'x-api-key' => $account->apiKey->key,
        ]);
    }

    protected function generateFirstResponse(Password $password)
    {
        return $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json($this->method, $this->route);
    }

    protected function generateSecondResponse(Password $password, $firstResponse)
    {
        return $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $firstResponse),
        ]);
    }

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
            \strstr($password->account->identifier, '@', true),
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
