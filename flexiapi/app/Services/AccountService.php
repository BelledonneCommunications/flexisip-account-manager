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

namespace App\Services;

use App\Account;
use App\AccountCreationToken;
use App\ActivationExpiration;
use App\EmailChangeCode;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Http\Requests\Account\Create\Request as CreateRequest;
use App\Http\Requests\Account\Update\Request as UpdateRequest;
use App\Libraries\OvhSMS;
use App\Mail\NewsletterRegistration;
use App\Mail\RecoverByCode;
use App\Mail\RegisterValidation;
use App\PhoneChangeCode;
use Illuminate\Support\Facades\Log;

use App\Rules\WithoutSpaces;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AccountService
{
    public function __construct(public bool $api = true)
    {
        // Load the hooks if they exists
        $accountServiceHooks = config_path('account_service_hooks.php');

        if (file_exists($accountServiceHooks)) {
            require_once($accountServiceHooks);
        }
    }

    /**
     * Account creation
     */
    public function store(CreateRequest $request): Account
    {
        $account = new Account();
        $account->username = $request->get('username');
        $account->activated = false;
        $account->domain = config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->created_at = Carbon::now();
        $account->user_agent = config('app.name');
        $account->dtmf_protocol = $request->get('dtmf_protocol');

        if ($request->asAdmin) {
            $account->email = $request->get('email');
            $account->display_name = $request->get('display_name');
            $account->activated = $request->has('activated') ? (bool)$request->get('activated') : false;
            $account->domain = resolveDomain($request);
            $account->user_agent = $request->header('User-Agent') ?? config('app.name');
            $account->admin = $request->has('admin') && (bool)$request->get('admin');
        }

        if ($account->activated == false) {
            $account->confirmation_key = Str::random(WebAuthenticateController::$emailCodeSize);
        }

        $account->save();

        if ($request->asAdmin) {
            if ((!$request->has('activated') || !(bool)$request->get('activated'))
            && $request->has('confirmation_key_expires')) {
                $actionvationExpiration = new ActivationExpiration();
                $actionvationExpiration->account_id = $account->id;
                $actionvationExpiration->expires = $request->get('confirmation_key_expires');
                $actionvationExpiration->save();
            }

            if ($request->has('dictionary')) {
                foreach ($request->get('dictionary') as $key => $value) {
                    $account->setDictionaryEntry($key, $value);
                }
            }

            $account->phone = $request->get('phone');
            $account->save();
        }

        $account->updatePassword($request->get('password'), $request->get('algorithm'));

        if ($request->api && !$request->asAdmin) {
            $token = AccountCreationToken::where('token', $request->get('account_creation_token'))->first();
            $token->consume();
            $token->account_id = $account->id;
            $token->save();

            Log::channel('events')->info('API: AccountCreationToken redeemed', ['account_creation_token' => $token->toLog()]);
        }

        Log::channel('events')->info(
            $request->asAdmin
                ? 'Account Service as Admin: Account created'
                : 'Account Service: Account created',
            ['id' => $account->identifier]
        );

        if (!$request->api) {
            if (!empty(config('app.newsletter_registration_address')) && $request->has('newsletter')) {
                Mail::to(config('app.newsletter_registration_address'))->send(new NewsletterRegistration($account));
            }
        }

        if (function_exists('accountServiceAccountCreatedHook')) {
            $account->refresh();
            accountServiceAccountCreatedHook($request, $account);
        }

        return Account::withoutGlobalScopes()->find($account->id);
    }

    /**
     * Account update
     */
    public function update(UpdateRequest $request, int $accountId): ?Account
    {
        $account = Account::findOrFail($accountId);

        if ($request->asAdmin) {
            $account->username = $request->get('username') ?? $account->username;
            $account->domain = $request->has('domain') ? resolveDomain($request) : $account->domain;
            $account->email = $request->get('email');
            $account->display_name = $request->get('display_name');
            $account->dtmf_protocol = $request->get('dtmf_protocol');
            $account->user_agent = $request->header('User-Agent') ?? config('app.name');
            $account->admin = $request->has('admin') && (bool)$request->get('admin');

            if ($request->api && $request->has('admin')) {
                $account->admin = (bool)$request->get('admin');
            }

            if (!$request->api && $request->has('role')) {
                $account->setRole($request->get('role'));
            }

            if (!$request->api && $request->has('activated')) {
                $account->activated = $request->get('activated') == 'true';
            }

            if (!$request->api && $request->has('blocked')) {
                $account->blocked = $request->get('blocked') == 'true';
            }

            if ($request->get('password')) {
                $account->updatePassword(
                    $request->get('password'),
                    $request->api ? $request->get('algorithm') : null
                );
            }

            $account->phone = $request->get('phone');
            $account->save();

            if ($request->has('dictionary')) {
                $account->dictionaryEntries()->delete();

                foreach ($request->get('dictionary') as $key => $value) {
                    $account->setDictionaryEntry($key, $value);
                }
            }
        }

        Log::channel('events')->info(
            $request->asAdmin
                ? 'Account Service as Admin: Account updated'
                : 'Account Service: Account updated',
            ['id' => $account->identifier]
        );

        if (function_exists('accountServiceAccountEditedHook')) {
            $account->refresh();
            accountServiceAccountEditedHook($request, $account);
        }

        return Account::withoutGlobalScopes()->find($account->id);
    }

    /**
     * Account destroy
     */
    public function destroy(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->delete();

        Log::channel('events')->info(
            'Account Service: Account destroyed',
            ['id' => $account->identifier]
        );

        if (function_exists('accountServiceAccountDestroyedHook')) {
            accountServiceAccountDestroyedHook($request, $account);
        }
    }

    /**
     * Link a phone number to an account
     */
    public function requestPhoneChange(Request $request)
    {
        $request->validate([
            'phone' => [
                'required', 'unique:accounts,phone',
                'unique:accounts,username',
                new WithoutSpaces(), 'starts_with:+'
            ]
        ]);

        $account = $request->user();

        $phoneChangeCode = $account->phoneChangeCode ?? new PhoneChangeCode();
        $phoneChangeCode->account_id = $account->id;
        $phoneChangeCode->phone = $request->get('phone');
        $phoneChangeCode->code = generatePin();
        $phoneChangeCode->fillRequestInfo($request);
        $phoneChangeCode->save();

        Log::channel('events')->info('Account Service: Account phone change requested by SMS', ['id' => $account->identifier]);

        $ovhSMS = new OvhSMS();
        $ovhSMS->send($request->get('phone'), 'Your ' . config('app.name') . ' validation code is ' . $phoneChangeCode->code);
    }

    public function updatePhone(Request $request): ?Account
    {
        $request->validate($this->api ? [
            'code' => 'required|digits:4'
        ] : [
            'number_1' => 'required|digits:1',
            'number_2' => 'required|digits:1',
            'number_3' => 'required|digits:1',
            'number_4' => 'required|digits:1'
        ]);

        $code = $this->api ? $request->get('code')
            : $request->get('number_1') . $request->get('number_2') . $request->get('number_3') . $request->get('number_4');

        $account = $request->user();

        $phoneChangeCode = $account->phoneChangeCode()->firstOrFail();

        if ($phoneChangeCode->code == $code) {
            $account->phone = $phoneChangeCode->phone;
            $account->activated = true;
            $account->save();

            Log::channel('events')->info('Account Service: Account phone changed using SMS', ['id' => $account->identifier]);

            $account->refresh();

            $phoneChangeCode->consume();

            return $account;
        }

        if ($this->api) {
            abort(403);
        }

        return null;
    }

    /**
     * Link an email to an account
     */
    public function requestEmailChange(Request $request)
    {
        $rules = ['required', 'email', Rule::notIn([$request->user()->email])];

        if (config('app.account_email_unique')) {
            array_push($rules, Rule::unique('accounts', 'email'));
        }

        $request->validate([
            'email' => $rules,
        ]);

        $account = $request->user();

        $emailChangeCode = $account->emailChangeCode ?? new EmailChangeCode();
        $emailChangeCode->account_id = $account->id;
        $emailChangeCode->email = $request->get('email');
        $emailChangeCode->code = generatePin();
        $emailChangeCode->fillRequestInfo($request);
        $emailChangeCode->save();

        Log::channel('events')->info('Account Service: Account email change requested by email', ['id' => $account->identifier]);

        Mail::to($emailChangeCode->email)->send(new RegisterValidation($account));
    }

    public function updateEmail(Request $request): ?Account
    {
        $request->validate($this->api ? [
            'code' => 'required|digits:4'
        ] : [
            'number_1' => 'required|digits:1',
            'number_2' => 'required|digits:1',
            'number_3' => 'required|digits:1',
            'number_4' => 'required|digits:1'
        ]);

        $code = $this->api ? $request->get('code')
            : $request->get('number_1') . $request->get('number_2') . $request->get('number_3') . $request->get('number_4');

        $account = $request->user();

        $emailChangeCode = $account->emailChangeCode()->firstOrFail();
        if ($emailChangeCode->validate($code)) {
            $account->email = $emailChangeCode->email;
            $account->save();

            Log::channel('events')->info('Account Service: Account email changed using email', ['id' => $account->identifier]);

            $emailChangeCode->consume();

            $account->activated = true;
            $account->save();

            $account->refresh();

            return $account;
        }

        if ($this->api) {
            abort(403);
        }

        return null;
    }

    /**
     * Account recovery
     */

    public function recoverByEmail(Account $account): Account
    {
        $account = $this->recoverAccount($account);

        Mail::to($account)->send(new RecoverByCode($account));

        Log::channel('events')->info('Account Service: Sending recovery email', ['id' => $account->identifier]);

        return $account;
    }

    public function recoverByPhone(Account $account): Account
    {
        $account = $this->recoverAccount($account);

        $ovhSMS = new OvhSMS();
        $ovhSMS->send($account->phone, 'Your ' . config('app.name') . ' validation code is ' . $account->recovery_code);

        Log::channel('events')->info('Account Service: Sending recovery SMS', ['id' => $account->identifier]);

        return $account;
    }

    private function recoverAccount(Account $account): Account
    {
        $account->recover();
        $account->provision();

        $account->refresh();

        return $account;
    }
}
