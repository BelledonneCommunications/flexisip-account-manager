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
use App\AccountAction;
use App\Rules\NoUppercase;

class AccountActionController extends Controller
{
    public function create(int $id)
    {
        $account = Account::findOrFail($id);

        return view('admin.account.action.create_edit', [
            'action' => new AccountAction,
            'account' => $account
        ]);
    }

    public function store(Request $request, int $id)
    {
        $account = Account::findOrFail($id);

        $request->validate([
            'key' => ['required', 'alpha_dash', new NoUppercase],
            'code' => ['required', 'alpha_num', new NoUppercase]
        ]);

        $accountAction = new AccountAction;
        $accountAction->account_id = $account->id;
        $accountAction->key = $request->get('key');
        $accountAction->code = $request->get('code');
        $accountAction->save();

        $request->session()->flash('success', 'Action successfully created');
        Log::channel('events')->info('Web Admin: Account action created', ['id' => $account->identifier, 'action' => $accountAction->key]);

        return redirect()->route('admin.account.show', $accountAction->account);
    }

    public function edit(int $id, int $actionId)
    {
        $account = Account::findOrFail($id);

        $accountAction = $account->actions()
            ->where('id', $actionId)
            ->firstOrFail();

        return view('admin.account.action.create_edit', [
            'action' => $accountAction,
            'account' => $account
        ]);
    }

    public function update(Request $request, int $id, int $actionId)
    {
        $account = Account::findOrFail($id);

        $request->validate([
            'key' => ['alpha_dash', new NoUppercase],
            'code' => ['alpha_num', new NoUppercase]
        ]);

        $accountAction = $account->actions()
            ->where('id', $actionId)
            ->firstOrFail();
        $accountAction->key = $request->get('key');
        $accountAction->code = $request->get('code');
        $accountAction->save();

        $request->session()->flash('success', 'Action successfully updated');
        Log::channel('events')->info('Web Admin: Account action updated', ['id' => $account->identifier, 'action' => $accountAction->key]);

        return redirect()->route('admin.account.show', $account);
    }

    public function delete(int $id, int $actionId)
    {
        $account = Account::findOrFail($id);

        return view('admin.account.action.delete', [
            'action' => $account->actions()
                                ->where('id', $actionId)
                                ->firstOrFail()
        ]);
    }

    public function destroy(Request $request, int $id, int $actionId)
    {
        $account = Account::findOrFail($id);

        $accountAction = $account->actions()
                        ->where('id', $actionId)
                        ->firstOrFail();
        $accountAction->delete();

        $request->session()->flash('success', 'Action successfully destroyed');

        Log::channel('events')->info('Web Admin: Account action deleted', ['id' => $accountAction->account->identifier, 'action_id' => $accountAction->key]);

        return redirect()->route('admin.account.show', $accountAction->account);
    }
}
