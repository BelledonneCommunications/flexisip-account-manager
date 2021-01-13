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

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

use App\Account;
use App\Alias;
use App\Helpers\Utils;
use App\Libraries\OvhSMS;
use App\Mail\PasswordAuthentication;

class AuthenticateController extends Controller
{
    public static $emailCodeSize = 13;

    public function login(Request $request)
    {
        return view('account.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required|exists:external.accounts,username',
            'password' => 'required'
        ]);

        $account = Account::where('username', $request->get('username'))
                          ->first();

        if (!$account) {
            return redirect()->back()->withErrors(['authentication' => 'The account doesn\'t exists']);
        }

        // Try out the passwords
        foreach ($account->passwords as $password) {
            if (hash_equals(
                $password->password,
                Utils::bchash($request->get('username'), config('app.sip_domain'), $request->get('password'), $password->algorithm)
            )) {
                Auth::login($account);
                return redirect()->route('account.panel');
            }
        }

        return redirect()->back()->withErrors(['authentication' => 'Wrong username or password']);
    }

    public function loginEmail(Request $request)
    {
        return view('account.login.email', [
            'domain' => '@' . config('app.sip_domain')
        ]);
    }

    public function authenticateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:external.accounts,email',
            'username' => [
                'required',
                Rule::exists('external.accounts', 'username')->where(function ($query) use ($request) {
                    $query->where('email', $request->get('email'));
                }),
            ],
            'g-recaptcha-response'  => 'required|captcha',
        ]);

        /**
         * Because several accounts can have the same email
         */
        $account = Account::where('email', $request->get('email'))
                          ->first();
        $account->confirmation_key = Str::random(self::$emailCodeSize);
        $account->save();

        Mail::to($account)->send(new PasswordAuthentication($account));

        return view('account.authenticate.email', [
            'account' => $account
        ]);
    }

    public function validateEmail(Request $request, string $code)
    {
        $request->merge(['code' => $code]);
        $request->validate(['code' => 'required|size:'.self::$emailCodeSize]);

        $account = Account::where('confirmation_key', $code)->firstOrFail();
        $account->confirmation_key = null;

        // If there is already a password set, we directly activate the account
        if ($account->passwords()->count() != 0) {
            $account->activated = true;
        }

        $account->save();

        Auth::login($account);

        // Ask the user to set a password
        if (!$account->activated) {
            return redirect()->route('account.password');
        }

        $request->session()->flash('success', 'Your account creation process is now finished.');

        return redirect()->route('account.panel');
    }

    public function loginPhone(Request $request)
    {
        return view('account.login.phone');
    }

    public function authenticatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|starts_with:+|phone:AUTO',
            'g-recaptcha-response'  => 'required|captcha',
        ]);

        $account = Account::where('username', $request->get('phone'))
                          ->first();

        // Try alias
        if (!$account) {
            $alias = Alias::where('alias', $request->get('phone'))->first();

            if ($alias) {
                $account = $alias->account;
            }
        }

        if (!$account) {
            return view('account.login_phone')->withErrors([
                'phone' => 'Phone number not found'
            ]);
        }

        $account->confirmation_key = Utils::generatePin();
        $account->save();

        $ovhSMS = new OvhSMS;
        $ovhSMS->send($request->get('phone'), 'Your '.config('app.name').' validation code is '.$account->confirmation_key);

        // Ask the user to set a password
        if (!$account->activated) {
            return redirect()->route('account.password');
        }

        return view('account.authenticate.phone', [
            'account' => $account
        ]);
    }

    public function validatePhone(Request $request)
    {
        $request->validate([
            'account_id' => 'required',
            'code' => 'required|digits:4'
        ]);

        $account = Account::where('id', $request->get('account_id'))
                          ->firstOrFail();

        if ($account->confirmation_key != $request->get('code')) {
            return view('account.login_phone')->withErrors([
                'code' => 'Wrong code'
            ]);
        }

        $account->confirmation_key = null;
        $account->save();

        Auth::login($account);
        return redirect()->route('account.panel');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('account.login');
    }
}
