<?php

namespace Tests\Feature;

use App\Account;
use App\Space;
use App\SpaceSsoServer;
use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SsoUser;
use Tests\TestCase;

class AccountSSOAuthentificationTest extends TestCase
{
    protected $route = '/login/sso';
    protected $redirectRoute = '/login/sso/redirect';
    protected $method = 'GET';
    protected $serverPrivateKeyPem = null;
    protected $serverPublicKeyPem = null;

    public function setUp(): void
    {
        parent::setUp();

        $keys = openssl_pkey_new(array("private_key_bits" => 4096, "private_key_type" => OPENSSL_KEYTYPE_RSA));
        $this->serverPublicKeyPem = openssl_pkey_get_details($keys)['key'];
        openssl_pkey_export($keys, $this->serverPrivateKeyPem);

    }
    public function testLoginSso(): void
    {
        $space = Space::factory()->create();
        $this->get($this->route)->assertStatus(403);

        SpaceSsoServer::factory()->withSpaceId($space->id)->create([
            'public_key' => $this->serverPublicKeyPem,
        ]);
        $space->refresh();

        $this->get($this->route)->assertStatus(302);
    }

    public function testHandleRedirect(): void
    {
        $account = Account::factory()->create();
        $space = $account->space;
        $ssoUser = new SsoUser;
        $ssoUser->email = fake()->email();
        $account->email = fake()->email();
        $account->save();

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($ssoUser);

        // SSO disabled
        $this->get($this->redirectRoute)
            ->assertStatus(403);

        SpaceSsoServer::factory()->withSpaceId($space->id)->create([
            'public_key' => $this->serverPublicKeyPem,
        ]);
        $space->refresh();

        /**
         * Auto_provisioning off
         */

        // User does not exist
        $this->get($this->redirectRoute)
            ->assertRedirect('login')
            ->assertSessionHasErrors(['sso_not_found']);

        $ssoUser->email = $account->email;
        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($ssoUser);

        // Existing user
        $this->get($this->redirectRoute)
            ->assertRedirect(route('account.home'));

        /**
         * Auto_provisioning on
         */

        SpaceSsoServer::where('space_id', $space->id)->update(['auto_provisioning' => true]);
        $space->load('ssoServer');

        // Without Roles - User does not exist
        $ssoUser->email = fake()->email();
        $clock = new FrozenClock(new DateTimeImmutable);
        $ssoUser->token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder
                ->withClaim('email', $account->email)
                ->withClaim('sip_identity', 'sip:' . fake()->email())
        )->toString();

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($ssoUser);

        $this->get($this->redirectRoute)
            ->assertRedirect('login')
            ->assertSessionHasErrors(['sso_not_found']);

        // Without Roles - Existing user
        $ssoUser->token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder
                ->withClaim('email', $account->email)
                ->withClaim('sip_identity', $account->sip_uri)
        )->toString();

        $ssoUser->email = $account->email;

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($ssoUser);

        $this->get($this->redirectRoute)
            ->assertRedirect('login')
            ->assertSessionHasErrors(['sso_not_found']);
        $this->assertFalse($account->fresh()->activated);

        // Username already taken
        $ssoUser->email = fake()->email();

        $ssoUser->token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder
                ->withClaim('sip_identity', $account->sip_uri)
                ->withClaim('realm_access', ['roles' => [$space->ssoServer->role_provisioning]])
        )->toString();

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($ssoUser);

        $this->get($this->redirectRoute)
            ->assertRedirect('login')
            ->assertSessionHasErrors(['sso_not_found']);

        $this->assertEquals(
            'Username already taken. Please contact your administrator.',
            session('errors')->get('sso_not_found')[0]
        );

        // Account creation
        $ssoUser->email = fake()->email();

        $ssoUser->token = (new JwtFacade(clock: $clock))->issue(
            new Sha256,
            InMemory::plainText($this->serverPrivateKeyPem),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ): Builder => $builder
                ->withClaim('sip_identity', 'sip:' . $ssoUser->email)
                ->withClaim('realm_access', ['roles' => [$space->ssoServer->role_provisioning]])
        )->toString();

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($ssoUser);

        $this->get($this->redirectRoute)
            ->assertRedirect(route('account.home'));

        $newAccount = Account::where('email', $ssoUser->email)->first();
        assert($newAccount != null);

    }
}
