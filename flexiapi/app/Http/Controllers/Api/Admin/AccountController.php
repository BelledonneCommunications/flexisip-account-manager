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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Account;
use App\ActivationExpiration;
use App\Admin;
use App\Alias;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Rules\WithoutSpaces;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return Account::without(['passwords', 'admin'])->paginate(20);
    }

    public function show(Request $request, $id)
    {
        return Account::without(['passwords', 'admin'])->findOrFail($id)->makeVisible(['confirmation_key']);
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
            'username' => [
                'required',
                Rule::unique('accounts', 'username')->where(function ($query) {
                    $query->where('domain', config('app.sip_domain'));
                }),
                'filled',
            ],
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required|filled',
            'email' => 'email',
            'admin' => 'boolean|nullable',
            'activated' => 'boolean|nullable',
            'confirmation_key_expires' => [
                'date_format:Y-m-d H:i:s',
                'nullable',
            ],
            'phone' => [
                'unique:aliases,alias',
                'unique:accounts,username',
                new WithoutSpaces, 'starts_with:+'
            ]
        ]);

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->activated = $request->has('activated')
            ? (bool)$request->get('activated')
            : false;
        $account->domain = config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');

        if (!$request->has('activated') || !(bool)$request->get('activated')) {
            $account->confirmation_key = Str::random(WebAuthenticateController::$emailCodeSize);
        }

        $account->save();

        if ((!$request->has('activated') || !(bool)$request->get('activated'))
         && $request->has('confirmation_key_expires')) {
            $actionvationExpiration = new ActivationExpiration;
            $actionvationExpiration->account_id = $account->id;
            $actionvationExpiration->expires = $request->get('confirmation_key_expires');
            $actionvationExpiration->save();
        }

        $account->updatePassword($request->get('password'), $request->get('algorithm'));

        if ($request->has('admin') && (bool)$request->get('admin')) {
            $admin = new Admin;
            $admin->account_id = $account->id;
            $admin->save();
        }

        if ($request->has('phone')) {
            $alias = new Alias;
            $alias->alias = $request->get('phone');
            $alias->domain = config('app.sip_domain');
            $alias->account_id = $account->id;
            $alias->save();
        }

        // Full reload
        $account = Account::withoutGlobalScopes()->find($account->id);

        Log::channel('events')->info('API: Admin: Account created', ['id' => $account->identifier]);

        return response()->json($account->makeVisible(['confirmation_key']));
    }
}
