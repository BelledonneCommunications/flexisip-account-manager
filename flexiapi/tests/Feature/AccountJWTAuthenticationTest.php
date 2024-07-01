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

class AccountJWTAuthenticationTest extends TestCase
{
    protected $route = '/provisioning';
    protected $accountRoute = '/provisioning/me';
    protected $method = 'GET';
    protected $serverPrivateKeyPem = null;
    protected $serverPublicKeyPem = null;

    public function setUp(): void
    {
        parent::setUp();

        $keys = openssl_pkey_new(array("private_key_bits" => 4096,"private_key_type" => OPENSSL_KEYTYPE_RSA));
        $this->serverPublicKeyPem = openssl_pkey_get_details($keys)['key'];
        openssl_pkey_export($keys, $this->serverPrivateKeyPem);
    }

    public function testBaseProvisioning()
    {
        # JWT is disabled if Sodium is not loaded
        if (!extension_loaded('sodium')) return;

        $password = Password::factory()->create();

        config()->set('services.jwt.rsa_public_key_pem', $this->serverPublicKeyPem);

        $this->get($this->route)->assertStatus(400);

        $clock = new FrozenClock(new DateTimeImmutable());

        $token = (new JwtFacade(null, $clock))->issue(
            new Sha256(),
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $password->account->email)
        );

        $this->checkToken($token);

        // SIP identifier

        // This line shoudn't be required, but the pipeline doesn't get the default value somehow
        config()->set('services.jwt.sip_identifier', 'sip_identity');

        $token = (new JwtFacade(null, $clock))->issue(
            new Sha256(),
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('sip_identity', 'sip:' . $password->account->username . '@' . $password->account->domain)
        );

        $this->checkToken($token);

        // Custom SIP identifier
        $otherIdentifier = 'sip_other_identifier';
        config()->set('services.jwt.sip_identifier', $otherIdentifier);

        $token = (new JwtFacade(null, $clock))->issue(
            new Sha256(),
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim($otherIdentifier, 'sip:' . $password->account->username . '@' . $password->account->domain)
        );

        $this->checkToken($token);

        // Sha512
        $token = (new JwtFacade(null, $clock))->issue(
            new Sha512(),
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $password->account->email)
        );

        $this->checkToken($token);

        // Expired token
        $oldClock = new FrozenClock(new DateTimeImmutable('2022-06-24 22:51:10'));

        $token = (new JwtFacade(null, $oldClock))->issue(
            new Sha256(),
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder->withClaim('email', $password->account->email)
        );

        $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->toString(),
                'x-linphone-provisioning' => true,
            ])
            ->get($this->accountRoute)
            ->assertStatus(403);

        // Expired token

        $token = (new JwtFacade(null, $clock))->issue(
            new Sha256(),
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

        $keys = openssl_pkey_new(array("private_key_bits" => 4096,"private_key_type" => OPENSSL_KEYTYPE_RSA));
        openssl_pkey_export($keys, $wrongServerPrivateKeyPem);

        $wrongToken = (new JwtFacade(null, $clock))->issue(
            new Sha256(),
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
            ->assertStatus(403);
    }

    private function checkToken(UnencryptedToken $token): void
    {
        $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->toString(),
                'x-linphone-provisioning' => true,
            ])
            ->get($this->accountRoute)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');
    }
}
