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
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class AccountAlstomTest extends AccountJWTAuthenticationTest
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testAlstomProvisioning()
    {
        # JWT is disabled if Sodium is not loaded
        if (!extension_loaded('sodium'))
            return;

        $password = Password::factory()->create();

        //$domain = 'sip_provisioning.example.com';
        $bearer = 'authz_server="https://sso.test/", realm="sip.test.org"';

        //\App\Space::where('domain', $password->account->domain)->update(['host' => $domain]);
        //config()->set('app.sip_domain', $domain);
        config()->set('services.jwt.rsa_public_key_pem', $this->serverPublicKeyPem);

        $this->get($this->route)->assertStatus(400);

        // Accounts to provision
        $passwordAccount1 = Password::factory()->create();
        $passwordAccount2 = Password::factory()->create();

        $clock = new FrozenClock(new DateTimeImmutable());

        config()->set('services.jwt.sip_identifier', 'sip_identity');

        $token = (new JwtFacade(null, $clock))->issue(
            new Sha256(),
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn(
            Builder $builder,
            DateTimeImmutable $issuedAt
        ): Builder => $builder
                ->withClaim(
                    'sip_identity',
                    'sip:' . $password->account->username . '@' . $password->account->domain
                )
                ->withClaim(
                    'matching_accounts',
                    [
                        'sip:' . $passwordAccount1->account->identifier,
                        'sip:' . $passwordAccount2->account->identifier
                    ]
                )
        );

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertStatus(200)
            ->assertSee($passwordAccount1->account->username)
            ->assertSee($passwordAccount1->account->passwords()->first()->ha1);

        // Non existing accounts

        $token = (new JwtFacade(null, $clock))->issue(
            new Sha256(),
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn(
            Builder $builder,
            DateTimeImmutable $issuedAt
        ): Builder => $builder
                ->withClaim(
                    'sip_identity',
                    'sip:' . $password->account->username . '@' . $password->account->domain
                )
                ->withClaim(
                    'matching_accounts',
                    [
                        'sip:' . $passwordAccount1->account->identifier,
                        'sip:' . $passwordAccount2->account->identifier,
                        'sip:other@account.com'
                    ]
                )
        );

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->toString(),
            'x-linphone-provisioning' => true,
        ])
            ->get($this->accountRoute)
            ->assertStatus(400)
            ->dump();
    }
}
