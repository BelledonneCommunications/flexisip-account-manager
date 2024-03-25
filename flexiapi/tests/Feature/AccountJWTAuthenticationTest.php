<?php

namespace Tests\Feature;

use App\Password;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;
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

        $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->toString(),
                'x-linphone-provisioning' => true,
            ])
            ->get($this->accountRoute)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');

        // Sha512
        $token = (new JwtFacade(null, $clock))->issue(
            new Sha512(),
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
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');

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
}
