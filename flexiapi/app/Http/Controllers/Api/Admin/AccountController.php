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
use App\AccountTombstone;
use App\AccountType;
use App\ActivationExpiration;
use App\Admin;
use App\Alias;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Mail\PasswordAuthentication;
use App\Rules\BlacklistedUsername;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;
use App\Rules\WithoutSpaces;
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return Account::without(['passwords', 'admin'])->paginate(20);
    }

    public function show($id)
    {
        return Account::without(['passwords', 'admin'])->findOrFail($id)->makeVisible(['confirmation_key', 'provisioning_token']);
    }

    public function search(string $sip)
    {
        return Account::sip($sip)->firstOrFail();
    }

    public function searchByEmail(Request $request, string $email)
    {
        return Account::where('email', $email)->firstOrFail();
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);

        if (!$account->hasTombstone()) {
            $tombstone = new AccountTombstone;
            $tombstone->username = $account->username;
            $tombstone->domain = $account->domain;
            $tombstone->save();
        }

        Log::channel('events')->info('API Admin: Account destroyed', ['id' => $account->identifier]);

        $account->delete();
    }

    public function activate(int $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = true;
        $account->save();

        Log::channel('events')->info('API Admin: Account activated', ['id' => $account->identifier]);

        return $account;
    }

    public function deactivate(int $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = false;
        $account->save();

        Log::channel('events')->info('API Admin: Account deactivated', ['id' => $account->identifier]);

        return $account;
    }

    public function provision(int $id)
    {
        $account = Account::findOrFail($id);
        $account->provision();
        $account->save();

        Log::channel('events')->info('API Admin: Account provisioned', ['id' => $account->identifier]);

        return $account->makeVisible(['provisioning_token']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                new NoUppercase,
                new IsNotPhoneNumber,
                new BlacklistedUsername,
                new SIPUsername,
                Rule::unique('accounts', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', $this->resolveDomain($request));
                }),
                'filled',
            ],
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required|filled',
            'admin' => 'boolean|nullable',
            'activated' => 'boolean|nullable',
            'dtmf_protocol' => 'nullable|in:' . Account::dtmfProtocolsRule(),
            'confirmation_key_expires' => [
                'date_format:Y-m-d H:i:s',
                'nullable',
            ],
            'email' => config('app.account_email_unique')
                ? 'nullable|email|unique:accounts,email'
                : 'nullable|email',
            'phone' => [
                'unique:aliases,alias',
                'unique:accounts,username',
                new WithoutSpaces, 'starts_with:+'
            ]
        ]);

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->display_name = $request->get('display_name');
        $account->activated = $request->has('activated') ? (bool)$request->get('activated') : false;
        $account->ip_address = $request->ip();
        $account->dtmf_protocol = $request->get('dtmf_protocol');
        $account->creation_time = Carbon::now();
        $account->domain = $this->resolveDomain($request);
        $account->user_agent = $request->header('User-Agent') ?? config('app.name');

        if (!$request->has('activated') || !(bool)$request->get('activated')) {
            $account->confirmation_key = Str::random(WebAuthenticateController::$emailCodeSize);
            $account->provision();
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

        Log::channel('events')->info('API Admin: Account created', ['id' => $account->identifier]);

        return response()->json($account->makeVisible(['confirmation_key', 'provisioning_token']));
    }

    public function typeAdd(int $id, int $typeId)
    {
        if (Account::findOrFail($id)->types()->pluck('id')->contains($typeId)) {
            abort(403);
        }

        if (AccountType::findOrFail($typeId)) {
            return Account::findOrFail($id)->types()->attach($typeId);
        }
    }

    public function typeRemove(int $id, int $typeId)
    {
        if (!Account::findOrFail($id)->types()->pluck('id')->contains($typeId)) {
            abort(403);
        }

        return Account::findOrFail($id)->types()->detach($typeId);
    }

    public function recoverByEmail(int $id)
    {
        $account = Account::findOrFail($id);
        $account->provision();
        $account->confirmation_key = Str::random(WebAuthenticateController::$emailCodeSize);
        $account->save();

        Log::channel('events')->info('API Admin: Sending recovery email', ['id' => $account->identifier]);

        Mail::to($account)->send(new PasswordAuthentication($account));

        return response()->json($account->makeVisible(['confirmation_key', 'provisioning_token']));
    }
}
