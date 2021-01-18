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

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Account;
use App\Password;
use App\Helpers\Utils;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return Account::without(['passwords', 'admin'])->paginate(20);
    }

    public function show(Request $request, $id)
    {
        return Account::without(['passwords', 'admin'])->findOrFail($id);
    }

    public function destroy(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->delete();
    }

    public function activate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = true;
        $account->save();

        return $account;
    }

    public function deactivate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = false;
        $account->save();

        return $account;
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:external.accounts,username|filled',
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required|filled',
            'domain' => 'min:3',
            'activated' => 'boolean|nullable',
        ]);

        $algorithm = $request->has('password_sha256') ? 'SHA-256' : 'MD5';

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->activated = $request->has('activated')
            ? (bool)$request->get('activated')
            : false;
        $account->domain = $request->has('domain')
            ? $request->get('domain')
            : config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');

        if (!$request->has('activated') || !(bool)$request->get('activated')) {
            $account->confirmation_key = Str::random(WebAuthenticateController::$emailCodeSize);
        }

        $account->save();

        $password = new Password;
        $password->account_id = $account->id;
        $password->password = Utils::bchash($account->username, $account->domain, $request->get('password'), $request->get('algorithm'));
        $password->algorithm = $request->get('algorithm');
        $password->save();

        return response()->json($account);
    }
}
