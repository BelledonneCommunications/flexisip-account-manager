<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2019 Belledonne Communications SARL, All rights reserved.

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

namespace Tests\Feature;

use App\Helpers\Utils;
use App\Password;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticateDigestTest extends TestCase
{
    use RefreshDatabase;

    const ROUTE = '/api/ping';
    const METHOD = 'GET';
    const ALGORITHMS = ['md5' => 'MD5', 'sha256' => 'SHA-256'];

    public function testMandatoryFrom()
    {
        $password = factory(Password::class)->create();
        $response = $this->json(self::METHOD, self::ROUTE);
        $response->assertStatus(422);
    }

    public function testWrongFrom()
    {
        $password = factory(Password::class)->create();
        $response = $this->withHeaders([
            'From' => 'sip:missing@username',
        ])->json(self::METHOD, self::ROUTE);

        $response->assertStatus(404);
    }

    public function testAuthenticate()
    {
        $password = factory(Password::class)->create();
        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
        ])->json(self::METHOD, self::ROUTE);
        $response->assertStatus(401);
    }

    public function testMultiHash()
    {
        // Two password and we link the second to the first related account
        $passwordMD5 = factory(Password::class)->create();
        $passwordSHA256 = factory(Password::class)->states('sha256')->make();
        $passwordSHA256->account_id = $passwordMD5->account_id;
        $passwordSHA256->save();

        $response = $this->withHeaders([
            'From' => 'sip:'.$passwordMD5->account->identifier,
        ])->json(self::METHOD, self::ROUTE);

        $response->assertStatus(401);

        $this->assertStringContainsString('algorithm=MD5', $response->headers->all()['www-authenticate'][0]);
        $this->assertStringContainsString('algorithm=SHA-256', $response->headers->all()['www-authenticate'][1]);
    }

    public function testReplayNonce()
    {
        $password = factory(Password::class)->create();
        $response0 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json(self::METHOD, self::ROUTE);

        $response1 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response0),
        ])->json(self::METHOD, self::ROUTE);

        $response1->assertStatus(200);

        // We increment the nc
        $response2 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response1, 'md5', '00000002'),
        ])->json(self::METHOD, self::ROUTE);

        $response2->assertStatus(200);

        // We don't increment it
        $response3 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response2, 'md5', '00000002'),
        ])->json(self::METHOD, self::ROUTE);

        $response3->assertSee('Nonce replayed');
        $response3->assertStatus(401);
    }

    public function testClearedNonce()
    {
        $password = factory(Password::class)->create();
        $response1 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json(self::METHOD, self::ROUTE);

        $response2 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response1, 'md5', '00000001'),
        ])->json(self::METHOD, self::ROUTE);

        $response2->assertStatus(200);

        // We remove the account related nonce
        $password->account->nonces()->first()->delete();

        $response3 = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response2, 'md5', '00000002'),
        ])->json(self::METHOD, self::ROUTE);

        $response3->assertSee('Nonce invalid');
        $response3->assertStatus(401);
        $this->assertStringContainsString('algorithm=MD5', $response3->headers->all()['www-authenticate'][0]);
    }

    public function testAuthenticationMD5()
    {
        $password = factory(Password::class)->create();
        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json(self::METHOD, self::ROUTE);

        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response),
        ])->json(self::METHOD, self::ROUTE);

        $this->assertStringContainsString('algorithm=MD5', $response->headers->all()['www-authenticate'][0]);

        $response->assertStatus(200);
    }

    public function testAuthenticationSHA265()
    {
        $password = factory(Password::class)->states('sha256')->create();
        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json(self::METHOD, self::ROUTE);

        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response, 'sha256'),
        ])->json(self::METHOD, self::ROUTE);

        $this->assertStringContainsString('algorithm=SHA-256', $response->headers->all()['www-authenticate'][0]);

        $response->assertStatus(200);
    }

    public function testAuthenticationSHA265FromCLRTXT()
    {
        $password = factory(Password::class)->states('clrtxt')->create();
        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json(self::METHOD, self::ROUTE);

        // The server is generating all the available hash algorythms
        $this->assertStringContainsString('algorithm=MD5', $response->headers->all()['www-authenticate'][0]);
        $this->assertStringContainsString('algorithm=SHA-256', $response->headers->all()['www-authenticate'][1]);

        // Let's simulate a local hash for the clear password
        $hash = 'sha256';
        $password->password = hash(
            $hash,
            $password->account->username.':'.$password->account->domain.':'.$password->password
        );

        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response, $hash),
        ])->json(self::METHOD, self::ROUTE);

        $this->assertStringContainsString('algorithm=MD5', $response->headers->all()['www-authenticate'][0]);
        $this->assertStringContainsString('algorithm=SHA-256', $response->headers->all()['www-authenticate'][1]);

        $response->assertStatus(200);
    }

    public function testAuthenticationBadPassword()
    {
        $password = factory(Password::class)->create();
        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier
        ])->json(self::METHOD, self::ROUTE);
        $password->password = 'wrong';

        $response = $this->withHeaders([
            'From' => 'sip:'.$password->account->identifier,
            'Authorization' => $this->generateDigest($password, $response),
        ])->json(self::METHOD, self::ROUTE);

        $response->assertStatus(401);
    }

    private function generateDigest(Password $password, $response, $hash = 'md5', $nc = '00000001')
    {
        $challenge = \substr($response->headers->get('www-authenticate'), 7);
        $extractedChallenge = $this->extractAuthenticateHeader($challenge);

        $cnonce = Utils::generateNonce();

        $A1 = $password->password;
        $A2 = hash($hash, self::METHOD . ':' . self::ROUTE);
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
            self::ROUTE,
            $extractedChallenge['qop'],
            $response,
            $extractedChallenge['opaque'],
            self::ALGORITHMS[$hash],
        );

        return 'Digest ' . $digest;
    }

    private function extractAuthenticateHeader(string $string): array
    {
        preg_match_all(
            '@(realm|nonce|qop|opaque|algorithm)=[\'"]?([^\'",]+)@',
            $string,
            $array
        );

        return array_combine($array[1], $array[2]);
    }
}
