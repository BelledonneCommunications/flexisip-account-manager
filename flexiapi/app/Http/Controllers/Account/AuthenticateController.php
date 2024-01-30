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

use App\Account;
use App\Alias;
use App\AuthToken;

class AuthenticateController extends Controller
{
    public static $emailCodeSize = 13;

    public function login(Request $request)
    {
        if (auth()->user()) {
            return redirect()->route('account.dashboard');
        }

        return view('account.login', [
            'count' => Account::where('activated', true)->count()
        ]);
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
            return redirect()->back()->withErrors(['authentication' => 'Wrong username or password']);
        }

        // Try out the passwords
        foreach ($account->passwords as $password) {
            if (hash_equals(
                $password->password,
                bchash($account->username, $account->resolvedRealm, $request->get('password'), $password->algorithm)
            )) {
                Auth::login($account);
                return redirect()->route('account.dashboard');
            }
        }

        return redirect()->back()->withErrors(['authentication' => 'Wrong username or password']);
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
            $authToken->fillRequestInfo($request);
            $authToken->save();

            return redirect()->route('account.authenticate.auth_token', ['token' => $authToken->token]);
        }

        // If the $authToken was flashed by an authenticated user
        if ($authToken->account_id) {
            Auth::login($authToken->account);

            $authToken->delete();

            $request->session()->flash('success', 'Successfully authenticated');

            return redirect()->route('account.dashboard');
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
