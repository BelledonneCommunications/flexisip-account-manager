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
use App\Alias;

use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Http\Requests\Account\Create\Api\Request as ApiRequest;
use App\Libraries\OvhSMS;
use App\Mail\RegisterConfirmation;

use App\Rules\AccountCreationToken as RulesAccountCreationToken;
use App\Rules\BlacklistedUsername;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;
use App\Rules\WithoutSpaces;
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
     * /!\ Dangerous endpoint, disabled by default
     */
    public function phoneInfo(Request $request, string $phone)
    {
        if (!config('app.dangerous_endpoints')) return abort(404);

        $request->merge(['phone' => $phone]);
        $request->validate([
            'phone' => ['required', new WithoutSpaces, 'starts_with:+']
        ]);

        $alias = Alias::where('alias', $phone)->first();
        $account = $alias
            ? $alias->account
            // Injecting the default sip domain to try to resolve the account
            : Account::sip($phone . '@' . config('app.sip_domain'))->firstOrFail();

        return \response()->json([
            'activated' => $account->activated,
            'realm' => $account->realm,
            'phone' => (bool)$alias
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
                'unique:aliases,alias',
                'unique:accounts,username',
                new WithoutSpaces, 'starts_with:+'
            ],
            'account_creation_token' => [
                'required',
                new RulesAccountCreationToken
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
        $account->user_agent = $request->header('User-Agent') ?? config('app.name');
        $account->save();

        $account->updatePassword($request->get('password'), $request->get('algorithm'));

        $token = AccountCreationToken::where('token', $request->get('account_creation_token'))->first();
        $token->consume();
        $token->account_id = $account->id;
        $token->save();

        Log::channel('events')->info('API: AccountCreationToken redeemed', ['token' => $request->get('account_creation_token')]);
        Log::channel('events')->info('API: Account created using the public endpoint', ['id' => $account->identifier]);

        // Send validation by phone
        if ($request->has('phone')) {
            $alias = new Alias;
            $alias->alias = $request->get('phone');
            $alias->domain = config('app.sip_domain');
            $alias->account_id = $account->id;
            $alias->save();

            $account->confirmation_key = generatePin();
            $account->save();

            Log::channel('events')->info('API: Account created using the public endpoint by phone', ['id' => $account->identifier]);

            $ovhSMS = new OvhSMS;
            $ovhSMS->send($request->get('phone'), 'Your ' . config('app.name') . ' creation code is ' . $account->confirmation_key);
        } elseif ($request->has('email')) {
            // Send validation by email
            $account->confirmation_key = Str::random(WebAuthenticateController::$emailCodeSize);
            $account->save();

            Log::channel('events')->info('API: Account created using the public endpoint by email', ['id' => $account->identifier]);

            try {
                Mail::to($account)->send(new RegisterConfirmation($account));
            } catch (\Exception $e) {
                Log::channel('events')->info('API: Public Register Confirmation email not sent, check errors log', ['id' => $account->identifier]);
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
                'required', new WithoutSpaces, 'starts_with:+'
            ],
            'account_creation_token' => [
                'required',
                new RulesAccountCreationToken
            ]
        ]);

        $alias = Alias::where('alias', $request->get('phone'))->first();
        $account = $alias
            ? $alias->account
            : Account::sip($request->get('phone') . '@' . config('app.sip_domain'))->firstOrFail();

        $account->confirmation_key = generatePin();
        $account->save();

        $token = AccountCreationToken::where('token', $request->get('account_creation_token'))->first();
        $token->consume();
        $token->account_id = $account->id;
        $token->save();

        Log::channel('events')->info('API: AccountCreationToken redeemed', ['token' => $request->get('account_creation_token')]);
        Log::channel('events')->info('API: Account recovery by phone', ['id' => $account->identifier]);

        $ovhSMS = new OvhSMS;
        $ovhSMS->send($request->get('phone'), 'Your ' . config('app.name') . ' recovery code is ' . $account->confirmation_key);
    }

    /**
     * /!\ Dangerous endpoint, disabled by default
     */
    public function recoverUsingKey(string $sip, string $recoveryKey)
    {
        if (!config('app.dangerous_endpoints')) return abort(404);

        $alias = Alias::sip($sip)->first();
        $account = $alias
            ? $alias->account
            : Account::sip($sip)->firstOrFail();

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
