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
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use App\Account;
use App\Token;
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

    public function store(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                Rule::unique('accounts', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', config('app.sip_domain'));
                }),
                'filled',
            ],
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required|filled',
            'domain' => 'min:3',
            'token' => [
                'required',
                Rule::exists('tokens', 'token')->where(function ($query) {
                    $query->where('used', false);
                }),
                'size:'.WebAuthenticateController::$emailCodeSize
            ]
        ]);

        $token = Token::where('token', $request->get('token'))->first();
        $token->used = true;
        $token->save();

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->activated = false;
        $account->domain = $request->has('domain')
            ? $request->get('domain')
            : config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');
        $account->confirmation_key = Str::random(WebAuthenticateController::$emailCodeSize);
        $account->save();

        $account->updatePassword($request->get('password'), $request->get('algorithm'));

        Log::channel('events')->info('API: Account created', ['id' => $account->identifier]);

        // Full reload
        return Account::withoutGlobalScopes()->find($account->id);
    }

    public function activateEmail(Request $request, string $sip)
    {
        $request->validate([
            'code' => 'required|size:'.WebAuthenticateController::$emailCodeSize
        ]);

        $account = Account::sip($sip)
                          ->where('confirmation_key', $request->get('code'))
                          ->firstOrFail();

        if ($account->activationExpired()) abort(403, 'Activation expired');

        $account->activated = true;
        $account->confirmation_key = null;
        $account->save();

        Log::channel('events')->info('API: Account activated by email', ['id' => $account->identifier]);

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

        if ($account->activationExpired()) abort(403, 'Activation expired');

        $account->activated = true;
        $account->confirmation_key = null;
        $account->save();

        Log::channel('events')->info('API: Account activated by phone', ['id' => $account->identifier]);

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
