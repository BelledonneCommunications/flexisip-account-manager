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
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use App\Account;
use App\AccountCreationToken;

use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Http\Requests\Account\Create\Api\Request as ApiRequest;
use App\Libraries\OvhSMS;
use App\Mail\RegisterConfirmation;

use App\Rules\AccountCreationToken as RulesAccountCreationToken;
use App\Rules\AccountCreationTokenNotExpired;
use App\Rules\BlacklistedUsername;
use App\Rules\FilteredPhone;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;
use App\Rules\PasswordAlgorithm;

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

    /**
     * /!\ Dangerous endpoint, disabled by default
     */
    public function phoneInfo(Request $request, string $phone)
    {
        if (!config('app.dangerous_endpoints')) return abort(404);

        $request->merge(['phone' => $phone]);
        $request->validate([
            'phone' => ['required', 'phone', new FilteredPhone]
        ]);

        $account = Account::where('domain', config('app.sip_domain'))
            ->where(function ($query) use ($phone) {
                $query->where('username', $phone)
                    ->orWhere('phone', $phone);
            })->firstOrFail();

        return \response()->json([
            'activated' => $account->activated,
            'realm' => $account->realm,
            'phone' => (bool)$account->phone
        ]);
    }

    /**
     * /!\ Dangerous endpoint, disabled by default
     * Store directly the account and alias in the DB and send a SMS or email for the validation
     */
    public function storePublic(Request $request)
    {
        if (!config('app.dangerous_endpoints')) return abort(404);

        $request->validate([
            'username' => [
                'required_without:phone',
                new NoUppercase,
                new BlacklistedUsername,
                new SIPUsername,
                Rule::unique('accounts', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', $request->has('domain') ? $request->get('domain') : config('app.sip_domain'));
                }),
                Rule::unique('accounts_tombstones', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', $request->has('domain') ? $request->get('domain') : config('app.sip_domain'));
                }),
                'filled',
            ],
            'algorithm' => ['required', new PasswordAlgorithm],
            'password' => 'required|filled',
            'domain' => 'min:3',
            'email' => config('app.account_email_unique')
                ? 'required_without:phone|email|unique:accounts,email'
                : 'required_without:phone|email',
            'phone' => [
                'required_without:email',
                'required_without:username',
                'phone',
                new FilteredPhone,
                'unique:accounts,phone',
                'unique:accounts,username',
            ],
            'account_creation_token' => [
                'required',
                new RulesAccountCreationToken,
                new AccountCreationTokenNotExpired
            ]
        ]);

        $account = new Account;
        $account->username = !empty($request->get('username'))
            ? $request->get('username')
            : $request->get('phone');
        $account->email = $request->get('email');
        $account->activated = false;
        $account->domain = $request->has('domain')
            ? $request->get('domain')
            : config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->created_at = Carbon::now();
        $account->user_agent = $request->header('User-Agent') ?? space()->name;
        $account->save();

        $account->updatePassword($request->get('password'), $request->get('algorithm'));

        $token = AccountCreationToken::where('token', $request->get('account_creation_token'))->first();
        $token->consume();
        $token->account_id = $account->id;
        $token->save();

        Log::channel('events')->info('API deprecated - Store public: AccountCreationToken redeemed', ['account_creation_token' => $token->toLog()]);
        Log::channel('events')->info('API deprecated - Store public: Account created', ['id' => $account->identifier]);

        // Send validation by phone
        if ($request->has('phone')) {
            $account->phone = $request->get('phone');
            $account->confirmation_key = generatePin();
            $account->save();

            Log::channel('events')->info('API deprecated: Account created using the public endpoint by phone', ['id' => $account->identifier]);

            $ovhSMS = new OvhSMS;
            $ovhSMS->send($request->get('phone'), 'Your ' . space()->name . ' creation code is ' . $account->confirmation_key);
        } elseif ($request->has('email')) {
            // Send validation by email
            $account->confirmation_key = Str::random(WebAuthenticateController::$emailCodeSize);
            $account->save();

            Log::channel('events')->info('API deprecated - Store public: Account created using the public endpoint by email', ['id' => $account->identifier]);

            try {
                Mail::to($account)->send(new RegisterConfirmation($account));
            } catch (\Exception $e) {
                Log::channel('events')->info('API deprecated - Store public: Public Register Confirmation email not sent, check errors log', ['id' => $account->identifier]);
                Log::error('Public Register Confirmation email not sent: ' . $e->getMessage());
            }
        }

        // Full reload
        return Account::withoutGlobalScopes()->find($account->id);
    }

    /**
     * /!\ Dangerous endpoint, disabled by default
     */
    public function recoverByPhone(Request $request)
    {
        if (!config('app.dangerous_endpoints')) return abort(404);

        $request->validate([
            'phone' => [
                'required', 'phone', new FilteredPhone, 'exists:accounts,phone'
            ],
            'account_creation_token' => [
                'required',
                new RulesAccountCreationToken,
                new AccountCreationTokenNotExpired
            ]
        ]);

        $account = Account::where('phone', $request->get('phone'))->first();
        $account->confirmation_key = generatePin();
        $account->save();

        $token = AccountCreationToken::where('token', $request->get('account_creation_token'))->first();
        $token->consume();
        $token->account_id = $account->id;
        $token->save();

        Log::channel('events')->info('API deprecated - Account recovery: AccountCreationToken redeemed', ['account_creation_token' => $token->toLog()]);
        Log::channel('events')->info('API deprecated - Account recovery: Account recovery by phone', ['id' => $account->identifier]);

        $ovhSMS = new OvhSMS;
        $ovhSMS->send($request->get('phone'), 'Your ' . space()->name . ' recovery code is ' . $account->confirmation_key);
    }

    /**
     * /!\ Dangerous endpoint, disabled by default
     */
    public function recoverUsingKey(string $sip, string $recoveryKey)
    {
        if (!config('app.dangerous_endpoints')) return abort(404);

        list($username, $domain) = explode('@', $sip);

        $account = Account::where('domain', $domain)
            ->where(function ($query) use ($username) {
                $query->where('username', $username)
                      ->orWhere('phone', $username);
            })->firstOrFail();

        $confirmationKey = $account->confirmation_key;
        $account->confirmation_key = null;

        if ($confirmationKey != $recoveryKey) abort(404);

        if ($account->activationExpired()) abort(403, 'Activation expired');

        $account->activated = true;
        $account->save();

        $account->passwords->each(function ($i, $k) {
            $i->makeVisible(['password']);
        });

        return $account;
    }

    public function store(ApiRequest $request)
    {
        return (new AccountService)->store($request);
    }

    /**
     * Deprecated
     */
    public function activateEmail(Request $request, string $sip)
    {
        // For retro-compatibility
        if ($request->has('code')) {
            $request->merge(['confirmation_key' => $request->get('code')]);
        }

        $request->validate([
            'confirmation_key' => 'required|size:' . WebAuthenticateController::$emailCodeSize
        ]);

        $account = Account::sip($sip)
            ->where('confirmation_key', $request->get('confirmation_key'))
            ->firstOrFail();

        if ($account->activationExpired()) abort(403, 'Activation expired');

        $account->activated = true;
        $account->confirmation_key = null;
        $account->save();

        Log::channel('events')->info('API: Account activated by email', ['id' => $account->identifier]);

        return $account;
    }

    /**
     * Deprecated
     */
    public function activatePhone(Request $request, string $sip)
    {
        // For retro-compatibility
        if ($request->has('code')) {
            $request->merge(['confirmation_key' => $request->get('code')]);
        }

        $request->validate([
            'confirmation_key' => 'required|digits:4'
        ]);

        $account = Account::sip($sip)
            ->where('confirmation_key', $request->get('confirmation_key'))
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
