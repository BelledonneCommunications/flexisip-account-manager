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

        if ($accountRecoveryToken->expired()) {
            abort(419, 'Token expired');
        }

        return view('account.recovery.show', [
            'method' => 'phone',
            'account_recovery_token' => $accountRecoveryToken->token,
            'phone' => $request->input('phone'),
            'domain' => resolveDomain($request)
        ]);
    }

    public function send(Request $request)
    {
        $rules = [
            'email' => 'required_without:phone|email|exists:accounts,email',
            'phone' => 'required_without:email|starts_with:+',
            'h-captcha-response' => captchaConfigured() ? 'required_with:email|HCaptcha' : '',
            'account_recovery_token' => 'required_with:phone',
        ];

        $account = null;

        if ($request->input('email')) {
            if (space()->unique_email == false) {
                $rules['username'] = 'required';
            }

            $request->validate($rules);

            $account = Account::where('email', $request->input('email'));

            /**
             * Because several accounts can have the same email
             */
            if (space()->unique_email == false) {
                $account = $account->where('username', $request->input('username'));
            }

            $account = $account->first();

            if (!$account) {
                $account = Account::where('phone', $request->input('username'))
                    ->where('email', $request->input('email'))
                    ->first();
            }
        } elseif ($request->input('phone')) {
            $account = Account::where('username', $request->input('phone'))->first();

            if (!$account) {
                $account = Account::where('phone', $request->input('phone'))->first();
            }
        }

        if (!$account) {
            return redirect()->back()->withErrors(['identifier' => __('A recovery code was sent if the account exists')]);
        }

        if ($account->failedRecentRecovery()) {
            return redirect()->back()->withErrors(['code' => __('Account recovered recently, try again later')]);
        }

        if ($request->input('email')) {
            $account = (new AccountService)->recoverByEmail($account, $request->input('email'));
        } elseif ($request->input('phone')) {
            $accountRecoveryToken = AccountRecoveryToken::where('token', $request->input('account_recovery_token'))
                ->where('used', false)
                ->first();

            if (!$accountRecoveryToken) {
                abort(403, 'Wrong Account Recovery Token');
            }

            $account = (new AccountService)->recoverByPhone($account, $request->input('phone'), $accountRecoveryToken);
        }

        return view('account.recovery.confirm', [
            'method' => $request->input('phone') ? 'phone' : 'email',
            'account_id' => Crypt::encryptString($account->id),
            'code' => $account->currentRecoveryCode
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

        $code = $request->input('number_1') . $request->input('number_2') . $request->input('number_3') . $request->input('number_4');

        $account = Account::where('id', Crypt::decryptString($request->input('account_id')))->firstOrFail();

        $recoveryCode = $account->currentRecoveryCode;

        if ($recoveryCode->expired() || $recoveryCode->attemptsLeft() == 0) {
            abort(419, __('The code has expired'));
        }

        if ($recoveryCode->code != $code) {
            $recoveryCode->attempts++;
            $recoveryCode->save();

            return view('account.recovery.confirm', [
                'method' => $request->input('phone') ? 'phone' : 'email',
                'account_id' => Crypt::encryptString($account->id),
                'code' => $recoveryCode
            ]);
        }

        $account->currentRecoveryCode->consume();

        Auth::login($account);
        return redirect()->route('account.password.update');
    }
}
