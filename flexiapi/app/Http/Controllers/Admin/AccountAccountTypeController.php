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

use App\Account;
use App\AccountType;

class AccountAccountTypeController extends Controller
{
    public function create(int $id)
    {
        $account = Account::findOrFail($id);

        return view('admin.account.account_type.create', [
            'account' => $account,
            'account_types' => AccountType::whereNotIn('id', function ($query) use ($account) {
                $query->select('account_type_id')
                      ->from('account_account_type')
                      ->where('account_id', $account->id);
            })->pluck('key', 'id')
        ]);
    }

    public function store(Request $request, int $id)
    {
        $account = Account::findOrFail($id);

        $request->validate([
            'account_type_id' => ['required', 'exists:account_types,id'],
        ]);

        $account->types()->detach($request->get('account_type_id'));
        $account->types()->attach($request->get('account_type_id'));

        $request->session()->flash('success', 'Type successfully added');
        Log::channel('events')->info('Web Admin: Account type attached', ['id' => $account->identifier, 'type_id' => $request->get('account_type_id')]);

        return redirect()->route('admin.account.show', $account);
    }

    public function destroy(Request $request, int $id, int $typeId)
    {
        $account = Account::findOrFail($id);

        $account->types()->detach($typeId);

        $request->session()->flash('success', 'Type successfully removed');
        Log::channel('events')->info('Web Admin: Account type detached', ['id' => $account->identifier, 'type_id' => $request->get('account_type_id')]);

        return redirect()->route('admin.account.show', $account);
    }
}
