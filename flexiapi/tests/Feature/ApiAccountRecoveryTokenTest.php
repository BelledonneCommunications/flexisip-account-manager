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
use App\RecoveryCode;
use App\Services\AccountService;
use App\Space;
use App\AccountRecoveryToken;
use Crypt;
use Tests\TestCase;

class ApiAccountRecoveryTokenTest extends TestCase
{
    private Space $space;

    protected $tokenRoute = '/api/account_recovery_tokens/send-by-push';
    protected $tokenRequestRoute = '/api/account_recovery_request_tokens';
    protected $method = 'POST';

    protected $pnProvider = 'fcm';
    protected $pnParam = 'param';
    protected $pnPrid = 'id';

    public function setUp(): void
    {
        parent::setUp();
        $this->space = Space::factory()->create();
    }

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
        $phone = '+3312345';

        $this->get($this->setSpaceOnRoute($this->space, route('account.recovery.show.phone', ['account_recovery_token' => 'bad_token'])))
            ->assertStatus(404);

        $this->get($this->setSpaceOnRoute($this->space, route('account.recovery.show.phone', ['account_recovery_token' => $token->token])))
            ->assertDontSee($phone)
            ->assertOk();

        $this->get($this->setSpaceOnRoute($this->space, route('account.recovery.show.phone', ['account_recovery_token' => $token->token, 'phone' => $phone])))
            ->assertSee($phone)
            ->assertOk();

        $token->consume();

        $this->get($this->setSpaceOnRoute($this->space, route('account.recovery.show.phone', ['account_recovery_token' => $token->token])))
            ->assertStatus(404);
    }

    public function testAttemptsRecoveryPage()
    {
        $phone = '+33667676767';
        $account = Account::factory()->admin()->state([
            'phone' => $phone,
        ])->create();

        $accountRecoveryToken = AccountRecoveryToken::factory()->create([
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ]);

        $account = (new AccountService)->recoverByPhone($account, $account->phone, $accountRecoveryToken);

        for ($i = 1; $i <= RecoveryCode::MAX_ATTEMPTS; $i++) {
            $this->post(route('account.recovery.confirm'), [
                'account_id' => Crypt::encryptString($account->id),
                'method' => 'phone',
                'number_1' => 1,
                'number_2' => 2,
                'number_3' => 3,
                'number_4' => 4,
            ])->assertOk()
                ->assertSeeText((RecoveryCode::MAX_ATTEMPTS - $i) . ' attempts left');
        }

        $this->post(route('account.recovery.confirm'), [
            'account_id' => Crypt::encryptString($account->id),
            'method' => 'phone',
            'number_1' => 1,
            'number_2' => 2,
            'number_3' => 3,
            'number_4' => 4,
        ])->assertStatus(419);
    }
}
