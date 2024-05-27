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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

use App\AccountType;
use App\Rules\NoUppercase;

class AccountTypeController extends Controller
{
    public function index()
    {
        return view('admin.account.type.index', ['types' => AccountType::all()]);
    }

    public function create()
    {
        return view('admin.account.type.create_edit', [
            'type' => new AccountType
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => ['required', 'alpha_dash', new NoUppercase, 'unique:account_types,key'],
        ]);

        $accountType = new AccountType;
        $accountType->key = $request->get('key');
        $accountType->save();

        return redirect()->route('admin.account.type.index');
    }

    public function edit(int $typeId)
    {
        return view('admin.account.type.create_edit', [
            'type' => AccountType::findOrFail($typeId)
        ]);
    }

    public function update(Request $request, int $typeId)
    {
        $request->validate([
            'key' => [
                'required',
                'alpha_dash',
                new NoUppercase,
                Rule::unique('account_types')->ignore($typeId)
            ]
        ]);

        $accountType = AccountType::findOrFail($typeId);
        $accountType->key = $request->get('key');
        $accountType->save();

        return redirect()->route('admin.account.type.index');
    }

    public function delete(int $typeId)
    {
        return view('admin.account.type.delete', [
            'type' => AccountType::findOrFail($typeId)
        ]);
    }

    public function destroy(int $typeId)
    {
        $type = AccountType::findOrFail($typeId);
        $type->delete();

        Log::channel('events')->info('Web Admin: Account type deleted', ['type' => $type->key]);

        return redirect()->route('admin.account.type.index');
    }
}
