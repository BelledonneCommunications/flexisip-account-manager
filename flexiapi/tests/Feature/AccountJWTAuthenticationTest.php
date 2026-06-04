<?php

/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2021 Belledonne Communications SARL, All rights reserved.

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

use App\Password;
use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use App\SpaceSsoServer;

class AccountJWTAuthenticationTest extends TestCase
{
    protected $route = '/provisioning';
    protected $accountRoute = '/provisioning/me';
    protected $method = 'GET';
    protected $serverPrivateKeyPem = null;
    protected $serverPublicKeyPem = null;

    protected $routeAccountMe = '/api/accounts/me';

    public function setUp(): void
    {
        parent::setUp();

        $keys = openssl_pkey_new(array("private_key_bits" => 4096, "private_key_type" => OPENSSL_KEYTYPE_RSA));
        $this->serverPublicKeyPem = openssl_pkey_get_details($keys)['key'];
        openssl_pkey_export($keys, $this->serverPrivateKeyPem);
    }

    public function testBaseProvisioning()
    {
        # JWT is disabled if Sodium is not loaded
        if (!extension_loaded('sodium')) {
            return;
        }

        $password = Password::factory()->create();
        $domain = 'sip_provisioning.example.com';

        $space = \App\Space::where('domain', $password->account->domain)->first();
        $space->update(['host' => $domain,]);

        SpaceSsoServer::factory()->withSpaceId($space->id)->create([
            'public_key' => $this->serverPublicKeyPem,
        ]);

        $space->refresh();

        config()->set('app.sip_domain', $domain);

        $this->get($this->route)->assertStatus(400);

        $clock = new FrozenClock(new DateTimeImmutable);

        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $password->account->email)
        );

        $this->checkToken($token);

        // SIP identifier

        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('sip_identity', 'sip:' . $password->account->username . '@' . $password->account->domain)
        );

        $this->checkToken($token);

        // Handle empty sso_sip_identifier
        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('sip_identity', 'sip:' . $password->account->username . '@' . $password->account->domain)
        );

        $this->checkToken($token);

        // Custom SIP identifier
        $otherIdentifier = 'sip_other_identifier';
        $space->ssoServer->update(['sip_identifier' => 'sip_other_identifier']);

        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim($otherIdentifier, 'sip:' . $password->account->username . '@' . $password->account->domain)
        );

        $this->checkToken($token);

        // Sha512
        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha512,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $password->account->email)
        );

        $this->checkToken($token);

        // Expired token
        $oldClock = new FrozenClock(new DateTimeImmutable('2022-06-24 22:51:10'));

        $token = (new JwtFacade(clock: $oldClock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $password->account->email)
        );

        $this->flushSession();
        Auth::logout();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertStatus(401);

        $this->assertStringContainsString('invalid_token', $response->headers->get('WWW-Authenticate'));

        // ...with the bearer
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertStatus(401);

        $this->assertStringContainsString($space->sso_authentication_bearer . ', ', $response->headers->get('WWW-Authenticate'));
        $this->assertStringContainsString('invalid_token', $response->headers->get('WWW-Authenticate'));

        // Wrong email
        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', 'unknow@man.org')
        );

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertStatus(403);

        // Wrong signature key
        $keys = openssl_pkey_new(array("private_key_bits" => 4096, "private_key_type" => OPENSSL_KEYTYPE_RSA));
        openssl_pkey_export($keys, $wrongServerPrivateKeyPem);

        $wrongToken = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($wrongServerPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $password->account->email)
        );

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $wrongToken->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertStatus(401);
    }

    public function testAuthBearerUrl()
    {
        # JWT is disabled if Sodium is not loaded
        if (!extension_loaded('sodium')) {
            return;
        }

        $password = Password::factory()->create();
        $space = \App\Space::where('domain', $password->account->domain)->first();

        SpaceSsoServer::factory()->withSpaceId($space->id)->create([
            'public_key' => $this->serverPublicKeyPem,
        ]);

        $space->refresh();

        $response = $this->json($this->method, $this->routeAccountMe)
            ->assertStatus(401);

        $this->assertStringContainsString(
            'Bearer ' . $space->sso_authentication_bearer,
            $response->headers->all()['www-authenticate'][0]
        );

        // Wrong From
        $this->withHeaders(['From' => 'sip:missing@username'])
            ->json($this->method, $this->routeAccountMe)
            ->assertStatus(401);

        $this->assertStringContainsString(
            'Bearer ' . $space->sso_authentication_bearer,
            $response->headers->all()['www-authenticate'][0]
        );

        // Wrong bearer message
        $this->withHeaders([
            'Authorization' => 'Bearer 1234'
        ])
            ->json($this->method, $this->routeAccountMe)
            ->assertStatus(401);

        $this->assertStringContainsString(
            'Bearer ' . $space->sso_authentication_bearer,
            $response->headers->all()['www-authenticate'][0]
        );
    }

    protected function checkToken(UnencryptedToken $token): void
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');
    }
}
