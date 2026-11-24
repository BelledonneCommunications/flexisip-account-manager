<?php

namespace Tests\Feature;

use App\Account;
use App\Space;
use App\SpaceOIDCAuthenticationConfiguration;
use App\SpaceOIDCAuthenticationConfigurationKey;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SsoUser;
use Tests\TestCase;

class AccountOIDCAuthentificationTest extends TestCase
{
    protected $route = '/login/oidc';
    protected $redirectRoute = '/login/oidc/redirect';
    protected $method = 'GET';

    public function setUp(): void
    {
        parent::setUp();
        $this->generateKeyPair();
    }

    public function testLoginSso(): void
    {
        $space = Space::factory()->create();

        $this->get($this->route)->assertStatus(403);

        SpaceOIDCAuthenticationConfiguration::factory()
            ->withSpaceId($space->id)
            ->has(SpaceOIDCAuthenticationConfigurationKey::factory(['public_key' => $this->serverPublicKeyPem]), 'keys')
            ->create();

        $this->get($this->route)->assertStatus(302);
    }

    public function testHandleRedirect(): void
    {
        $account = Account::factory()->create();
        $space = $account->space;
        $ssoUser = new SsoUser;
        $account->email = fake()->email();
        $account->save();

        // SSO disabled - middleware interception
        $this->get($this->redirectRoute)
            ->assertStatus(403);

        SpaceOIDCAuthenticationConfiguration::factory()
            ->withSpaceId($space->id)
            ->has(SpaceOIDCAuthenticationConfigurationKey::factory(['public_key' => $this->serverPublicKeyPem]), 'keys')
            ->create();
        $space->refresh();

        // ssoUser without email
        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($ssoUser);

        $this->get($this->redirectRoute)
            ->assertRedirect('login')
            ->assertSessionHasErrors(['sso_not_found']);

        // User does not exist
        $ssoUser->email = fake()->email();
        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($ssoUser);

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
    }
}
