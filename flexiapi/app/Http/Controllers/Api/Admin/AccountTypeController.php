<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2021 Belledonne Communications SARL, All rights reserved.

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
use App\Rules\NoUppercase;
use Illuminate\Http\Request;

use App\AccountType;

class AccountTypeController extends Controller
{
    public function index()
    {
        return AccountType::all();
    }

    public function get(int $accountTypeId)
    {
        return AccountType::where('id', $accountTypeId)
                      ->firstOrFail();
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => ['required', 'alpha_dash', new NoUppercase, 'unique:account_types,key'],
        ]);

        $accountType = new AccountType;
        $accountType->key = $request->get('key');
        $accountType->save();

        return $accountType;
    }

    public function update(Request $request, int $accountTypeId)
    {
        $request->validate([
            'key' => ['alpha_dash', new NoUppercase],
        ]);

        $accountType = AccountType::where('id', $accountTypeId)
                                  ->firstOrFail();
        $accountType->key = $request->get('key');
        $accountType->save();

        return $accountType;
    }

    public function destroy(int $accountTypeId)
    {
        return AccountType::where('id', $accountTypeId)
                          ->delete();
    }
}
