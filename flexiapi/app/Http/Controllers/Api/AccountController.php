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

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Account;

use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;

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

    public function activateEmail(Request $request, string $sip)
    {
        $request->validate([
            'code' => 'required|size:'.WebAuthenticateController::$emailCodeSize
        ]);

        $account = Account::sip($sip)
                          ->where('confirmation_key', $request->get('code'))
                          ->firstOrFail();
        $account->activated = true;
        $account->confirmation_key = null;

        $account->save();

        return $account;
    }

    public function activatePhone(Request $request, string $sip)
    {
        $request->validate([
            'code' => 'required|digits:4'
        ]);

        $account = Account::sip($sip)
                          ->where('confirmation_key', $request->get('code'))
                          ->firstOrFail();
        $account->activated = true;
        $account->confirmation_key = null;
        $account->save();

        return $account;
    }

    public function show(Request $request)
    {
        return Account::where('id', $request->user()->id)
                      ->without(['api_key', 'email_changed.new_email'])
                      ->first();
    }

    public function delete(Request $request)
    {
        return Account::where('id', $request->user()->id)
                      ->delete();
    }
}
