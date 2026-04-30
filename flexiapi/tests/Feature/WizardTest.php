<?php

namespace Tests\Feature;

use App\Account;
use App\Wizard;
use App\Space;

use Tests\TestCase;

class WizardTest extends TestCase
{
    protected Account $admin;
    protected Wizard $wizard;

    public function setUp(): void
    {
        parent::setUp();
        Space::factory()->create();
        $this->admin = Account::factory()->admin()->create();
        $this->admin->generateUserApiKey();
        $this->wizard = Wizard::factory()->create();
    }

    public function testCreateWizard(): void
    {
        $this->keyAuthenticated($this->admin)
            ->json('POST', 'api/wizard', [
                'provisioning_account_id' => 1,
                'sip' => 'sip:admin@sip.linphone.org',
                'linphone_action' => 'call',
                'linphone_use_sips' => false
            ])
            ->assertStatus(201);
    }

    public function testUriWithoutTokenAndParams(): void
    {
        $this->get(route('wizard.show'))->assertViewHas('uri', 'sip-linphone:?linphone-action=show');
    }

    public function testUriWithToken(): void
    {
        $this->get(route('wizard.show', $this->wizard->token))->assertViewHas(
            'uri',
            fn($uri) =>
            str_starts_with($uri, 'sip-linphone:john@sip.linphone.org?linphone-fetch-config=')
            && str_ends_with($uri, '&linphone-action=call&linphone-use-sips')
        );
    }

    public function testUriWithParams(): void
    {
        $this->get(route('wizard.show', [
            'sip' => 'john@sip.linphone.org',
            'linphone-action' => 'call'
        ]))->assertViewHas('uri', 'sip-linphone:john@sip.linphone.org?linphone-action=call');
    }
}
