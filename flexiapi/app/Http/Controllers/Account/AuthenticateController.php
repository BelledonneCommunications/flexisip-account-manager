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

use App\Account;
use App\AuthToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Create\Request as CreateRequest;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;

class AuthenticateController extends Controller
{
    public static $emailCodeSize = 13;

    public function login(Request $request)
    {
        if ($request->user()) {
            if ($request->user()->superAdmin) {
                return redirect()->route('admin.spaces.index');
            } elseif ($request->user()->admin) {
                return redirect()->route('admin.spaces.me');
            }

            return redirect()->route('account.dashboard');
        }

        return view('account.login', config('app.show_login_counter_temp') ? [
            'count' => Account::where('activated', true)->count()
        ] : []);
    }

    private function loginAndRedirect(Account $account)
    {
        Auth::login($account);
        return redirect()->route('account.home');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $account = Account::where('username', $request->get('username'))
            ->first();

        if (!$account) {
            $account = Account::where('phone', $request->get('username'))->first();
        }

        if (!$account) {
            return redirect()->back()->withErrors(['authentication' => __('Incorrect username or password')]);
        }

        // Try out the passwords
        foreach ($account->passwords as $password) {
            if (
                hash_equals(
                    $password->password,
                    bchash($account->username, $account->resolvedRealm, $request->get('password'), $password->algorithm)
                )
            ) {
                return $this->loginAndRedirect($account);
            }
        }

        return redirect()->back()->withErrors(['authentication' => __('Incorrect username or password')]);
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

            return redirect()->route('account.home');
        }

        return view('account.authenticate.auth_token', [
            'authToken' => $authToken
        ]);
    }

    public function loginSso()
    {
        return Socialite::driver('keycloak')->redirect();
    }

    public function handleSsoRedirect(Request $request)
    {
        $ssoUser = Socialite::driver('keycloak')->stateless()->user();

        if (space()->ssoServer?->auto_provisioning) {
            $token = (new Parser(new JoseEncoder))->parse($ssoUser->token);
            $realmAccess = $token->claims()->get('realm_access');

            $hasRole = $realmAccess
                && !empty($realmAccess['roles'])
                && in_array(space()->ssoServer->role_provisioning, $realmAccess['roles']);

            if (!$hasRole) {
                Account::where('email', $ssoUser->email)->update(['activated' => false]);
                return redirect('login')->withErrors(['sso_not_found' => __('You don\'t have access to this app, contact your administrator')]);
            }

            $sip = parseSIP($token->claims()->get(space()->ssoServer->sip_identifier));

            if (!$sip) {
                return redirect('login')->withErrors(['sso_not_found' => __('Incorrect username or password')]);
            }

            $account = Account::where('username', $sip[0])->first();

            if ($account && $account->email != $ssoUser->email) {
                return redirect('login')->withErrors(['sso_not_found' => __('Username already taken. Please contact your administrator.')]);
            }

            if (!$account) {
                $createRequest = CreateRequest::create('/', 'POST', [
                    'username' => $sip[0],
                    'email' => $ssoUser->email,
                    'password' => Str::random(12),
                    'asAdmin' => true,
                ]);

                $createRequest->space = $request->space;

                $accountService = new AccountService;
                $account = $accountService->store($createRequest);
            }

            return $this->loginAndRedirect($account);
        }

        $account = Account::where('email', $ssoUser->email)->first();

        if (!$account) {
            return redirect('login')->withErrors(['sso_not_found' => __('Incorrect username or password')]);
        }

        return $this->loginAndRedirect($account);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        if (!space()->ssoServer) {
            return redirect()->route('account.login');
        }

        return redirect(Socialite::driver('keycloak')->getLogoutUrl(route('account.login'), space()->ssoServer->client_id));
    }
}
