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
use App\AccountCreationRequestToken;
use App\AccountCreationToken;
use App\Http\Middleware\ValidateJSON;
use Tests\TestCase;
use Carbon\Carbon;

class ApiPushNotificationTest extends TestCase
{
    protected $tokenRoute = '/api/push_notification';
    protected $method = 'POST';

    protected $pnProvider = 'fcm';
    protected $pnParam = 'param';
    protected $pnPrid = 'id';
    protected $type = 'message';

    public function testCorrectParameters()
    {
        $account = Account::factory()->create();
        $account->generateApiKey();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->tokenRoute, [
                'pn_provider' => $this->pnProvider,
                'pn_param' => $this->pnParam,
                'pn_prid' => $this->pnPrid,
                'type' => $this->type,
                'call_id' => '@blabla@'
            ])->assertJsonValidationErrors(['call_id']);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->tokenRoute, [
                'pn_provider' => $this->pnProvider,
                'pn_param' => $this->pnParam,
                'pn_prid' => $this->pnPrid,
                'type' => $this->type
            ])->assertStatus(503);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->tokenRoute, [
                'pn_provider' => $this->pnProvider,
                'pn_param' => $this->pnParam,
                'pn_prid' => $this->pnPrid,
                'type' => $this->type,
                'call_id' => 'call_id-123'
            ])->assertStatus(503);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->tokenRoute, [
                'pn_provider' => $this->pnProvider,
                'pn_param' => 'ABCD1234.org.linphone.phone.voip',
                'pn_prid' => $this->pnPrid,
                'type' => $this->type,
                'call_id' => 'call_id-123'
            ])->assertStatus(503);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->tokenRoute, [
                'pn_provider' => $this->pnProvider,
                'pn_param' => '@blabla@',
                'pn_prid' => $this->pnPrid,
                'type' => $this->type,
                'call_id' => 'call_id-123'
            ])->assertJsonValidationErrors(['pn_param']);
    }
}
