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

use App\Account;
use App\Space;
use App\SpaceOIDCAuthenticationConfiguration;
use App\SpaceOIDCAuthenticationConfigurationKey;
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
        $this->generateKeyPair();
    }

    public function testSpaceAPI()
    {
        Space::factory()->create();

        $admin = Account::factory()->superAdmin()->create();
        $admin->generateUserApiKey();

        $oidcConfiguration = SpaceOIDCAuthenticationConfiguration::factory()
            ->withSpaceId($admin->space->id)
            ->has(SpaceOIDCAuthenticationConfigurationKey::factory(['public_key' => $this->serverPublicKeyPem]), 'keys')
            ->create();

        $this->keyAuthenticated($admin)
            ->json($this->method, '/api/spaces')
            ->assertJsonFragment(['audience' => $oidcConfiguration->audience])
            ->assertJsonMissing(['public_key' => $this->serverPublicKeyPem])
            ->assertJsonMissing(['client_id' => $oidcConfiguration->client_id])
            ->assertJsonMissing(['client_secret' => $oidcConfiguration->client_secret])
            ->assertOk();
    }

    public function testBaseProvisioning()
    {
        # JWT is disabled if Sodium is not loaded
        if (!extension_loaded('sodium')) {
            return;
        }

        $account = Account::factory()->withEmail()->create();

        $secondSpace = Space::factory()->secondDomain()->create();
        $otherDomainAccount = Account::factory()->fromSpace($secondSpace)->create();

        SpaceOIDCAuthenticationConfiguration::factory()
            ->withSpaceId($account->space->id)
            ->has(SpaceOIDCAuthenticationConfigurationKey::factory(['public_key' => $this->serverPublicKeyPem]), 'keys')
            ->create();

        $account->space->refresh();

        $this->get($this->route)->assertStatus(400);

        $clock = new FrozenClock(new DateTimeImmutable);

        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $account->email)
        );

        $this->checkToken($token);

        // Other domain account

        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $otherDomainAccount->email)
        );

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertUnauthorized();

        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('sip_identity', $otherDomainAccount->sip_uri)
        );

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertUnauthorized();

        // SIP identifier

        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('sip_identity', $account->sip_uri)
        );

        $this->checkToken($token);

        // Handle empty sso_sip_identifier
        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('sip_identity', $account->sip_uri)
        );

        $this->checkToken($token);

        // Custom SIP identifier
        $otherIdentifier = 'sip_other_identifier';

        $account->space->oidcAuthenticationConfiguration->update(['sip_id_claim' => 'sip_other_identifier']);

        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim($otherIdentifier, $account->sip_uri)
        );

        $this->checkToken($token);

        // Sha512
        $token = (new JwtFacade(clock: $clock))->issue(
            new Sha512,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $account->email)
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
            ): Builder => $builder->withClaim('email', $account->email)
        );

        $this->flushSession();
        Auth::logout();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertUnauthorized();

        $this->assertStringContainsString('invalid_token', $response->headers->get('WWW-Authenticate'));

        // ...with the bearer
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertUnauthorized();

        $this->assertStringContainsString($account->space->oidc_authentication_bearer . ', ', $response->headers->get('WWW-Authenticate'));
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
            ->assertUnauthorized();

        // Wrong signature key
        $keys = openssl_pkey_new(array("private_key_bits" => 4096, "private_key_type" => OPENSSL_KEYTYPE_RSA));
        openssl_pkey_export($keys, $wrongServerPrivateKeyPem);

        $wrongToken = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($wrongServerPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $account->email)
        );

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $wrongToken->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertUnauthorized();
    }

    public function testAuthBearerUrl()
    {
        # JWT is disabled if Sodium is not loaded
        if (!extension_loaded('sodium')) {
            return;
        }

        $account = Account::factory()->create();
        $space = \App\Space::where('domain', $account->domain)->first();

        SpaceOIDCAuthenticationConfiguration::factory()
            ->withSpaceId($space->id)
            ->has(SpaceOIDCAuthenticationConfigurationKey::factory(['public_key' => $this->serverPublicKeyPem]), 'keys')
            ->create();

        $response = $this->json($this->method, $this->routeAccountMe)
            ->assertUnauthorized();

        $this->assertStringContainsString(
            'Bearer ' . $space->oidc_authentication_bearer,
            $response->headers->all()['www-authenticate'][0]
        );

        // Wrong From
        $this->withHeaders(['From' => 'sip:missing@username'])
            ->json($this->method, $this->routeAccountMe)
            ->assertUnauthorized();

        $this->assertStringContainsString(
            'Bearer ' . $space->oidc_authentication_bearer,
            $response->headers->all()['www-authenticate'][0]
        );

        // Wrong bearer message
        $this->withHeaders([
            'Authorization' => 'Bearer 1234'
        ])
            ->json($this->method, $this->routeAccountMe)
            ->assertUnauthorized();

        $this->assertStringContainsString(
            'Bearer ' . $space->oidc_authentication_bearer,
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
            ->assertSee('http://www.linphone.org/xsds/lpconfig.xsd');
    }
}
