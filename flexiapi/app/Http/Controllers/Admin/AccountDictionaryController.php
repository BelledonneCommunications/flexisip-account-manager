<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2023 Belledonne Communications SARL, All rights reserved.

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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Account;
use App\AccountDictionaryEntry;

class AccountDictionaryController extends Controller
{
    public function index(int $accountId)
    {
        return view(
            'admin.account.dictionary.index',
            [
                'account' => Account::findOrFail($accountId)
            ]
        );
    }

    public function create(int $accountId)
    {
        return view('admin.account.dictionary.create_edit', [
            'account' => Account::findOrFail($accountId),
            'entry' => new AccountDictionaryEntry
        ]);
    }

    public function store(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);

        $request->validate([
            'key' => 'required',
            'value' => 'required'
        ]);

        $account->setDictionaryEntry($request->get('key'), $request->get('value'));

        if (function_exists('accountServiceAccountEditedHook')) {
            $account->refresh();
            accountServiceAccountEditedHook($request, $account);
        }

        return redirect()->route('admin.account.dictionary.index', $account->id);
    }

    public function edit(int $accountId, string $key)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.dictionary.create_edit', [
            'account' => $account,
            'entry' => $account->dictionaryEntries()->where('key', $key)->firstOrFail()
        ]);
    }

    public function update(Request $request, int $accountId, int $entryId)
    {
        $request->validate([
            'value' => 'required'
        ]);

        $account = Account::findOrFail($accountId);

        $entry = $account->dictionaryEntries()->findOrFail($entryId);
        $entry->value = $request->get('value');
        $entry->save();

        if (function_exists('accountServiceAccountEditedHook')) {
            $account->refresh();
            accountServiceAccountEditedHook($request, $account);
        }

        return redirect()->route('admin.account.dictionary.index', $account->id);
    }

    public function delete(int $accountId, string $key)
    {
        $account = Account::findOrFail($accountId);

        return view(
            'admin.account.dictionary.delete',
            [
                'account' => $account,
                'entry' =>  $account->dictionaryEntries()->where('key', $key)->firstOrFail()
            ]
        );
    }

    public function destroy(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->dictionaryEntries()->where('key', $request->get('key'))->delete();

        return redirect()->route('admin.account.dictionary.index', $account);
    }
}
