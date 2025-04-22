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

namespace App\Http\Controllers\Api\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Create\Api\Request as ApiRequest;

use App\Account;
use App\Services\AccountService;

class AccountController extends Controller
{
    /**
     * Public information on a specific account
     */
    public function info(Request $request, string $sip)
    {
        $account = Account::sip($sip)->firstOrFail();

        return \response()->json([
            'activated' => $account->activated,
            'realm' => $account->realm
        ]);
    }

    /**
     * Get services credentials
     */
    public function turnService(Request $request)
    {
        if (hasCoturnConfigured()) {
            list($username, $password) = array_values(getCoTURNCredentials());

            return [
                'username' => $username,
                'password' => $password,
                'ttl' => config('app.coturn_session_ttl_minutes') * 60,
                'uris' => [
                    'turn:' . config('app.coturn_server_host'),
                ]
            ];
        }

        return abort(404, 'No TURN service configured');
    }

    public function store(ApiRequest $request)
    {
        return (new AccountService)->store($request);
    }

    public function show(Request $request)
    {
        return Account::where('id', $request->user()->id)
            ->without(['api_key', 'email_changed.new_email'])
            ->first();
    }

    public function provision(Request $request)
    {
        $account = $request->user();
        $account->provision();
        $account->save();

        Log::channel('events')->info('API: Account provisioned', ['id' => $account->identifier]);

        return $account->makeVisible(['provisioning_token']);
    }

    public function delete(Request $request)
    {
        $request->user()->createTombstone();

        (new AccountService)->destroy($request, $request->user()->id);

        return true;
    }
}
