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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Account;
use App\AccountTombstone;
use App\AccountType;
use App\ActivationExpiration;
use App\ContactsList;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Rules\PasswordAlgorithm;
use App\Services\AccountService;

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

    public function searchByEmail(string $email)
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

    public function block(int $id)
    {
        $account = Account::findOrFail($id);
        $account->blocked = true;
        $account->save();

        Log::channel('events')->info('API Admin: Account blocked', ['id' => $account->identifier]);

        return $account;
    }

    public function unblock(int $id)
    {
        $account = Account::findOrFail($id);
        $account->blocked = false;
        $account->save();

        Log::channel('events')->info('API Admin: Account unblocked', ['id' => $account->identifier]);

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

    public function store(CreateAccountRequest $request)
    {
        return (new AccountService)->store($request, asAdmin: true)->makeVisible(['confirmation_key', 'provisioning_token']);
    }

    public function update(UpdateAccountRequest $request, int $accountId)
    {
        $request->validate([
            'algorithm' => ['required', new PasswordAlgorithm],
            'admin' => 'boolean|nullable',
            'activated' => 'boolean|nullable'
        ]);

        $account = Account::findOrFail($accountId);
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->display_name = $request->get('display_name');
        $account->dtmf_protocol = $request->get('dtmf_protocol');
        $account->domain = resolveDomain($request);
        $account->user_agent = $request->header('User-Agent') ?? config('app.name');

        $account->save();

        $account->updatePassword($request->get('password'), $request->get('algorithm'));
        $account->admin = $request->has('admin') && (bool)$request->get('admin');
        $account->phone = $request->get('phone');

        // Full reload
        $account = Account::withoutGlobalScopes()->find($account->id);

        Log::channel('events')->info('API Admin: Account updated', ['id' => $account->identifier]);

        return $account->makeVisible(['confirmation_key', 'provisioning_token']);
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

    public function contactsListAdd(int $id, int $contactsListId)
    {
        if (Account::findOrFail($id)->contactsLists()->pluck('id')->contains($contactsListId)) {
            abort(403);
        }

        if (ContactsList::findOrFail($contactsListId)) {
            return Account::findOrFail($id)->contactsLists()->attach($contactsListId);
        }
    }

    public function contactsListRemove(int $id, int $contactsListId)
    {
        if (!Account::findOrFail($id)->contactsLists()->pluck('id')->contains($contactsListId)) {
            abort(403);
        }

        return Account::findOrFail($id)->contactsLists()->detach($contactsListId);
    }
}
