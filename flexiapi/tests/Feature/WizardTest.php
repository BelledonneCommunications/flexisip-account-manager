<?php

namespace Tests\Feature;

use App\Account;
use App\Wizard;

use Tests\TestCase;

class WizardTest extends TestCase
{
    protected Account $admin;
    protected Wizard $wizard;

    public function testCreateWizard(): void
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $account = Account::factory()->create();

        $sip = 'sip:admin@sip.linphone.org';

        $response = $this->keyAuthenticated($admin)
            ->json('POST', 'api/wizard', [
                'provisioning_account_id' => $account->id,
                'sip' => $sip,
                'linphone_action' => 'call',
                'linphone_use_sips' => false
            ])
            ->assertStatus(201);

        // Move to the web side

        $this->flushHeaders();

        $this->get(route('wizard.show', $response->json('token')))->assertViewHas(
            'uri',
            fn($uri) => str_starts_with($uri, 'sip-linphone:' . stripSipProtocol($sip) . '?linphone-action=call&linphone-fetch-config=')
        );

        // SIPs

        $response = $this->keyAuthenticated($admin)
            ->json('POST', 'api/wizard', [
                'provisioning_account_id' => $account->id,
                'sip' => $sip,
                'linphone_action' => 'bye',
                'linphone_use_sips' => true
            ])
            ->assertStatus(201);

        $this->flushHeaders();

        $this->get(route('wizard.show', $response->json('token')))->assertViewHas(
            'uri',
            fn($uri) => str_starts_with($uri, 'sip-linphone:' . stripSipProtocol($sip) . '?linphone-action=bye&linphone-fetch-config=')
            && str_ends_with($uri, '&linphone-use-sips=true')
        );

        // Empty

        $response = $this->keyAuthenticated($admin)
            ->json('POST', 'api/wizard', [
                'provisioning_account_id' => null,
                'sip' => $sip,
                'linphone_action' => null,
                'linphone_use_sips' => false
            ])
            ->assertStatus(201);

        $this->flushHeaders();

        $this->get(route('wizard.show', $response->json('token')))->assertViewHas(
            'uri',
            'sip-linphone:' . stripSipProtocol($sip) . '?linphone-action=show'
        );

        // Custom parameters

        $this->get(route('wizard.show', [
            'sip' => $sip,
            'linphone-action' => 'decline'
        ]))->assertViewHas(
            'uri',
            'sip-linphone:' . stripSipProtocol($sip) . '?linphone-action=decline'
        );

        $this->get(route('wizard.show', [
            'sip' => $sip,
            'linphone-use-sips' => 'whatever'
        ]))->assertViewHas(
            'uri',
            'sip-linphone:' . stripSipProtocol($sip) . '?linphone-action=show&linphone-use-sips=true'
        );
    }
}
