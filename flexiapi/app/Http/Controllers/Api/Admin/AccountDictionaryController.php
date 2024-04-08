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

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Account;

use Illuminate\Http\Request;

class AccountDictionaryController extends Controller
{
    public function index(int $accountId)
    {
        return Account::findOrFail($accountId)->dictionary;
    }

    public function show(int $accountId, string $key)
    {
        return Account::findOrFail($accountId)->dictionaryEntries()->where('key', $key)->first();
    }

    public function set(Request $request, int $accountId, string $key)
    {
        $request->validate([
            'value' => 'required'
        ]);

        return Account::findOrFail($accountId)->setDictionaryEntry($key, $request->get('value'));
    }

    public function destroy(int $accountId, string $key)
    {
        return Account::findOrFail($accountId)->dictionaryEntries()->where('key', $key)->delete();
    }
}
