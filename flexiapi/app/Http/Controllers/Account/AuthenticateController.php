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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

use App\Account;
use App\Alias;
use App\AuthToken;
use App\Libraries\OvhSMS;
use App\Mail\PasswordAuthentication;

class AuthenticateController extends Controller
{
    public static $emailCodeSize = 13;

    public function login(Request $request)
    {
        if (auth()->user()) {
            return redirect()->route('account.panel');
        }

        return view('account.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $account = Account::where('username', $request->get('username'))
            ->first();

        // Try alias
        if (!$account) {
            $alias = Alias::where('alias', $request->get('username'))->first();

            if ($alias) {
                $account = $alias->account;
            }
        }

        if (!$account) {
            return redirect()->back()->withErrors(['authentication' => 'The account doesn\'t exists']);
        }

        // Try out the passwords
        foreach ($account->passwords as $password) {
            if (hash_equals(
                $password->password,
                bchash($account->username, $account->resolvedRealm, $request->get('password'), $password->algorithm)
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

    /**
     * Display the form
     */
    public function authenticateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:accounts,email',
            'username' => [
                'required'
            ],
            'g-recaptcha-response'  => 'required|captcha',
        ]);

        /**
         * Because several accounts can have the same email
         */
        $account = Account::where('username', $request->get('username'));

        if (config('app.account_email_unique') == false) {
            $account = $account->where('email', $request->get('email'));
        }

        $account = $account->first();

        // Try alias
        if (!$account) {
            $alias = Alias::where('alias', $request->get('username'))->first();

            if ($alias && $alias->account->email == $request->get('email')) {
                $account = $alias->account;
            }
        }

        if (!$account) {
            return redirect()->back()->withErrors(['authentication' => 'The account doesn\'t exists']);
        }

        $account->confirmation_key = Str::random(self::$emailCodeSize);
        $account->save();

        Mail::to($account)->send(new PasswordAuthentication($account));

        return redirect()->route('account.check.email', $account->identifier);
    }

    /**
     * A page that check if the email was validated and reload if not
     */
    public function checkEmail(Request $request, string $sip)
    {
        if (auth()->user()) {
            return redirect()->route('account.panel');
        }

        $account = Account::sip($sip)->firstOrFail();

        return view('account.authenticate.email', [
            'account' => $account
        ]);
    }

    public function validateEmail(Request $request, string $code)
    {
        $request->merge(['code' => $code]);
        $request->validate(['code' => 'required|size:' . self::$emailCodeSize]);

        $account = Account::where('confirmation_key', $code)->first();

        if (!$account) {
            return redirect()->route('account.login_email');
        }

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

        return redirect()->route('account.panel');
    }

    public function loginPhone(Request $request)
    {
        return view('account.login.phone');
    }

    public function authenticatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|starts_with:+',
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
            return redirect()->back()->withErrors([
                'phone' => 'Invalid phone number'
            ]);
        }

        $account->confirmation_key = generatePin();
        $account->save();

        $ovhSMS = new OvhSMS;
        $ovhSMS->send($request->get('phone'), 'Your ' . config('app.name') . ' validation code is ' . $account->confirmation_key);

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
            return redirect()->back()->withErrors([
                'code' => 'Wrong code'
            ]);
        }

        $account->confirmation_key = null;
        $account->save();

        Auth::login($account);
        return redirect()->route('account.panel');
    }

    public function loginAuthToken(Request $request, ?string $token = null)
    {
        $authToken = null;

        if (!empty($token)) {
            $authToken = AuthToken::where('token', $token)->valid()->first();
        }

        if ($authToken == null) {
            $authToken = new AuthToken;
            $authToken->token = Str::random(32);
            $authToken->save();

            return redirect()->route('account.authenticate.auth_token', ['token' => $authToken->token]);
        }

        // If the $authToken was flashed by an authenticated user
        if ($authToken->account_id) {
            Auth::login($authToken->account);

            $authToken->delete();

            $request->session()->flash('success', 'Successfully authenticated');

            return redirect()->route('account.panel');
        }

        return view('account.authenticate.auth_token', [
            'authToken' => $authToken
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('account.login');
    }
}
