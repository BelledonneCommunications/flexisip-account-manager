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
use Illuminate\Support\Facades\Log;

use App\Account;
use App\AccountTombstone;
use App\AccountType;
use App\ContactsList;
use App\Http\Requests\Account\Create\Api\AsAdminRequest;
use App\Http\Requests\Account\Update\Api\AsAdminRequest as ApiAsAdminRequest;
use App\Services\AccountService;
use App\SipDomain;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return Account::without(['passwords', 'admin'])->with(['phoneChangeCode', 'emailChangeCode'])->paginate(20);
    }

    public function show(Request $request, $accountId)
    {
        $account = Account::without(['passwords', 'admin'])->with(['phoneChangeCode', 'emailChangeCode'])->findOrFail($accountId);

        if ($request->user()->admin) {
            if ($account->phoneChangeCode) {
                $account->phoneChangeCode->makeVisible(['code']);
            }

            if ($account->emailChangeCode) {
                $account->emailChangeCode->makeVisible(['code']);
            }
        }

        return $account;
    }

    public function search(string $sip)
    {
        return Account::sip($sip)->firstOrFail();
    }

    public function searchByEmail(string $email)
    {
        return Account::where('email', $email)->firstOrFail();
    }

    public function destroy(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);

        if (!$account->hasTombstone()) {
            $tombstone = new AccountTombstone();
            $tombstone->username = $account->username;
            $tombstone->domain = $account->domain;
            $tombstone->save();
        }

        (new AccountService())->destroy($request, $accountId);

        Log::channel('events')->info('API Admin: Account destroyed', ['id' => $account->identifier]);
    }

    public function activate(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->activated = true;
        $account->save();

        Log::channel('events')->info('API Admin: Account activated', ['id' => $account->identifier]);

        return $account;
    }

    public function deactivate(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->activated = false;
        $account->save();

        Log::channel('events')->info('API Admin: Account deactivated', ['id' => $account->identifier]);

        return $account;
    }

    public function block(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->blocked = true;
        $account->save();

        Log::channel('events')->info('API Admin: Account blocked', ['id' => $account->identifier]);

        return $account;
    }

    public function unblock(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->blocked = false;
        $account->save();

        Log::channel('events')->info('API Admin: Account unblocked', ['id' => $account->identifier]);

        return $account;
    }

    public function provision(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->provision();
        $account->save();

        Log::channel('events')->info('API Admin: Account provisioned', ['id' => $account->identifier]);

        return $account->makeVisible(['provisioning_token']);
    }

    public function store(AsAdminRequest $request)
    {
        // Create the missing SipDomain
        if ($request->user()->superAdmin
            && $request->has('domain')
            && !SipDomain::pluck('domain')->contains($request->get('domain'))) {
            $sipDomain = new SipDomain();
            $sipDomain->domain = $request->get('domain');
            $sipDomain->save();
        }

        return (new AccountService())->store($request)->makeVisible(['confirmation_key', 'provisioning_token']);
    }

    public function update(ApiAsAdminRequest $request, int $accountId)
    {
        $account = (new AccountService())->update($request, $accountId);

        Log::channel('events')->info('API Admin: Account updated', ['id' => $account->identifier]);

        return $account->makeVisible(['confirmation_key', 'provisioning_token']);
    }

    public function typeAdd(int $accountId, int $typeId)
    {
        if (Account::findOrFail($accountId)->types()->pluck('id')->contains($typeId)) {
            abort(403);
        }

        if (AccountType::findOrFail($typeId)) {
            return Account::findOrFail($accountId)->types()->attach($typeId);
        }
    }

    public function typeRemove(int $accountId, int $typeId)
    {
        if (!Account::findOrFail($accountId)->types()->pluck('id')->contains($typeId)) {
            abort(403);
        }

        return Account::findOrFail($accountId)->types()->detach($typeId);
    }

    public function contactsListAdd(int $accountId, int $contactsListId)
    {
        if (Account::findOrFail($accountId)->contactsLists()->pluck('id')->contains($contactsListId)) {
            abort(403);
        }

        if (ContactsList::findOrFail($contactsListId)) {
            return Account::findOrFail($accountId)->contactsLists()->attach($contactsListId);
        }
    }

    public function contactsListRemove(int $accountId, int $contactsListId)
    {
        if (!Account::findOrFail($accountId)->contactsLists()->pluck('id')->contains($contactsListId)) {
            abort(403);
        }

        return Account::findOrFail($accountId)->contactsLists()->detach($contactsListId);
    }
}
