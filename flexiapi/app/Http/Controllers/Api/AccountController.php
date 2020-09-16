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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Account;
use App\Password;
use App\Helpers\Utils;

class AccountController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:external.accounts,username|filled',
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required|filled',
            'domain' => 'min:3',
        ]);

        $algorithm = $request->has('password_sha256') ? 'SHA-256' : 'MD5';

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->activated = true;
        $account->domain = $request->has('domain')
            ? $request->get('domain')
            : config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');
        $account->save();

        $password = new Password;
        $password->account_id = $account->id;
        $password->password = Utils::bchash($account->username, $account->domain, $request->get('password'), $request->get('algorithm'));
        $password->algorithm = $request->get('algorithm');
        $password->save();

        return response()->json($account);
    }
}
