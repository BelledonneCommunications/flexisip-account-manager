<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

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
use App\AccountRecoveryToken;
use Tests\TestCase;
use Carbon\Carbon;
use App\Http\Middleware\IsWebPanelEnabled;

class ApiAccountRecoveryTokenTest extends TestCase
{
    protected $tokenRoute = '/api/account_recovery_tokens/send-by-push';
    protected $tokenRequestRoute = '/api/account_recovery_request_tokens';
    protected $method = 'POST';

    protected $pnProvider = 'fcm';
    protected $pnParam = 'param';
    protected $pnPrid = 'id';

    public function testMandatoryParameters()
    {
        $this->json($this->method, $this->tokenRoute)->assertStatus(422);
        $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => null,
            'pn_param' => null,
            'pn_prid' => null,
        ])->assertStatus(422);
    }

    public function testThrottling()
    {
        AccountRecoveryToken::factory()->create([
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ]);

        $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ])->assertStatus(503);

        // Redeem all the tokens
        AccountRecoveryToken::where('used', false)->update(['used' => true]);

        $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ])->assertStatus(429);
    }

    public function testTokenRecoveryPage()
    {
        $token = AccountRecoveryToken::factory()->create();
        $space = Space::factory()->create();
        $phone = '+3312345';

        $this->get($this->setSpaceOnRoute($space, route('account.recovery.show.phone', ['account_recovery_token' => 'bad_token'])))
            ->assertStatus(404);

        $this->get($this->setSpaceOnRoute($space, route('account.recovery.show.phone', ['account_recovery_token' => $token->token])))
            ->assertDontSee($phone)
            ->assertStatus(200);

        $this->get($this->setSpaceOnRoute($space, route('account.recovery.show.phone', ['account_recovery_token' => $token->token, 'phone' => $phone])))
            ->assertSee($phone)
            ->assertStatus(200);

        $token->consume();

        $this->get($this->setSpaceOnRoute($space, route('account.recovery.show.phone', ['account_recovery_token' => $token->token])))
            ->assertStatus(404);
    }
}
