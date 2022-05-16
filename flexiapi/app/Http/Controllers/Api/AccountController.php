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
use App\AccountTombstone;
use App\AccountCreationToken;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;

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
                new NoUppercase,
                new IsNotPhoneNumber,
                Rule::unique('accounts', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', $request->has('domain') && config('app.everyone_is_admin') && config('app.admins_manage_multi_domains')
                                                ? $request->get('domain')
                                                : config('app.sip_domain'));
                }),
                Rule::unique('accounts_tombstones', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', $request->has('domain') && config('app.everyone_is_admin') && config('app.admins_manage_multi_domains')
                                                ? $request->get('domain')
                                                : config('app.sip_domain'));
                }),
                'filled',
            ],
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required|filled',
            'dtmf_protocol' => 'nullable|in:' . Account::dtmfProtocolsRule(),
            'domain' => 'min:3',
            'account_creation_token' => [
                'required_without:token',
                Rule::exists('account_creation_tokens', 'token')->where(function ($query) {
                    $query->where('used', false);
                }),
                'size:'.WebAuthenticateController::$emailCodeSize
            ],
            // For retro-compatibility
            'token' => [
                'required_without:account_creation_token',
                Rule::exists('account_creation_tokens', 'token')->where(function ($query) {
                    $query->where('used', false);
                }),
                'size:'.WebAuthenticateController::$emailCodeSize
            ],
        ]);

        $token = AccountCreationToken::where('token', $request->get('token') ?? $request->get('account_creation_token'))->first();
        $token->used = true;
        $token->save();

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->activated = false;
        $account->domain = ($request->has('domain') && config('app.everyone_is_admin') && config('app.admins_manage_multi_domains'))
            ? $request->get('domain')
            : config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');
        $account->dtmf_protocol = $request->get('dtmf_protocol');
        $account->provisioning_token = Str::random(WebAuthenticateController::$emailCodeSize);
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
        if (!$request->user()->hasTombstone()) {
            $tombstone = new AccountTombstone;
            $tombstone->username = $request->user()->username;
            $tombstone->domain = $request->user()->domain;
            $tombstone->save();
        }

        return Account::where('id', $request->user()->id)
                      ->delete();
    }
}
