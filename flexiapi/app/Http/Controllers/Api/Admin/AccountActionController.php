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
use Illuminate\Http\Request;

use App\Account;
use App\AccountAction;
use App\Rules\NoUppercase;

class AccountActionController extends Controller
{
    public function index(int $id)
    {
        return Account::findOrFail($id)->actions;
    }

    public function get(int $id, int $actionId)
    {
        return Account::findOrFail($id)
                      ->actions()
                      ->where('id', $actionId)
                      ->firstOrFail();
    }

    public function store(Request $request, int $id)
    {
        $request->validate([
            'key' => ['required', 'alpha_dash', new NoUppercase],
            'code' => ['required', 'alpha_num', new NoUppercase],
            'protocol' => 'required|in:' . AccountAction::protocolsRule()
        ]);

        $accountAction = new AccountAction;
        $accountAction->account_id = Account::findOrFail($id)->id;
        $accountAction->key = $request->get('key');
        $accountAction->code = $request->get('code');
        $accountAction->protocol = $request->get('protocol');
        $accountAction->save();

        return $accountAction;
    }

    public function update(Request $request, int $id, int $actionId)
    {
        $request->validate([
            'key' => ['alpha_dash', new NoUppercase],
            'code' => ['alpha_num', new NoUppercase],
            'protocol' => 'in:' . AccountAction::protocolsRule()
        ]);

        $accountAction = Account::findOrFail($id)
                                ->actions()
                                ->where('id', $actionId)
                                ->firstOrFail();
        $accountAction->key = $request->get('key');
        $accountAction->code = $request->get('code');
        $accountAction->protocol = $request->get('protocol');
        $accountAction->save();

        return $accountAction;
    }

    public function destroy(int $id, int $actionId)
    {
        return Account::findOrFail($id)
                                 ->actions()
                                 ->where('id', $actionId)
                                 ->delete();
    }
}
