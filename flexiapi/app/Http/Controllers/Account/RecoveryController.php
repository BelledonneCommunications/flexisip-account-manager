<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2023 Belledonne Communications SARL, All rights reserved.

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

namespace App\Http\Controllers\Account;

use App\Account;
use App\AccountRecoveryToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Str;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;

class RecoveryController extends Controller
{
    public function showEmail(Request $request)
    {
        return view('account.recovery.show', [
            'method' => 'email',
            'domain' => resolveDomain($request)
        ]);
    }

    public function showPhone(Request $request, string $accountRecoveryToken)
    {
        $accountRecoveryToken = AccountRecoveryToken::where('token', $accountRecoveryToken)
            ->where('used', false)
            ->firstOrFail();

        return view('account.recovery.show', [
            'method' => 'phone',
            'account_recovery_token' => $accountRecoveryToken->token,
            'phone' => $request->get('phone'),
            'domain' => resolveDomain($request)
        ]);
    }

    public function send(Request $request)
    {
        $rules = [
            'email' => 'required_without:phone|email|exists:accounts,email',
            'phone' => 'required_without:email|starts_with:+',
            'h-captcha-response'  => captchaConfigured() ? 'required_with:email|HCaptcha' : '',
            'account_recovery_token' => 'required_with:phone',
        ];

        $account = null;

        if ($request->get('email')) {
            if (config('app.account_email_unique') == false) {
                $rules['username'] = 'required';
            }

            $request->validate($rules);

            $account = Account::where('email', $request->get('email'));

            /**
             * Because several accounts can have the same email
             */
            if (config('app.account_email_unique') == false) {
                $account = $account->where('username', $request->get('username'));
            }

            $account = $account->first();

            if (!$account) {
                $account = Account::where('phone', $request->get('username'))
                    ->where('email', $request->get('email'))
                    ->first();
            }
        } elseif ($request->get('phone')) {
            $account = Account::where('username', $request->get('phone'))->first();

            if (!$account) {
                $account = Account::where('phone', $request->get('phone'))->first();
            }
        }

        if (!$account) {
            return redirect()->back()->withErrors(['identifier' => __("The account doesn't exists")]);
        }

        if ($account->failedRecentRecovery()) {
            return redirect()->back()->withErrors(['code' => __('Account recovered recently, try again later')]);
        }

        if ($request->get('email')) {
            $account = (new AccountService)->recoverByEmail($account, $request->get('email'));
        } elseif ($request->get('phone')) {
            $accountRecoveryToken = AccountRecoveryToken::where('token', $request->get('account_recovery_token'))
                ->where('used', false)
                ->first();

            if (!$accountRecoveryToken) {
                abort(403, 'Wrong Account Recovery Token');
            }

            $account = (new AccountService)->recoverByPhone($account, $request->get('phone'), $accountRecoveryToken);
        }

        return view('account.recovery.confirm', [
            'method' => $request->get('phone') ? 'phone' : 'email',
            'account_id' => Crypt::encryptString($account->id)
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'account_id' => 'required',
            'method' => 'in:phone,email',
            'number_1' => 'required|digits:1',
            'number_2' => 'required|digits:1',
            'number_3' => 'required|digits:1',
            'number_4' => 'required|digits:1'
        ]);

        $code = $request->get('number_1') . $request->get('number_2') . $request->get('number_3') . $request->get('number_4');

        $account = Account::where('id', Crypt::decryptString($request->get('account_id')))->firstOrFail();

        if ($account->currentRecoveryCode->expired()) {
            return redirect()->route($request->get('method') == 'phone'
                ? 'account.recovery.show.phone'
                : 'account.recovery.show.email')->withErrors([
                'code' => __('The code has expired')
            ]);
        }

        if ($account->recovery_code != $code) {
            return redirect()->route($request->get('method') == 'phone'
                ? 'account.recovery.show.phone'
                : 'account.recovery.show.email')->withErrors([
                'code' => 'The code is not valid'
            ]);
        }

        $account->currentRecoveryCode->consume();

        Auth::login($account);
        return redirect()->route('account.password.update');
    }
}
