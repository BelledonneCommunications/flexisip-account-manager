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
        return $this->resolveAccount($id)->actions;
    }

    public function get(int $id, int $actionId)
    {
        return $this->resolveAccount($id)
                      ->actions()
                      ->where('id', $actionId)
                      ->firstOrFail();
    }

    public function store(Request $request, int $id)
    {
        $account = $this->resolveAccount($id);

        $request->validate([
            'key' => ['required', 'alpha_dash', new NoUppercase],
            'code' => ['required', 'alpha_num', new NoUppercase]
        ]);

        $accountAction = new AccountAction;
        $accountAction->account_id = $account->id;
        $accountAction->key = $request->get('key');
        $accountAction->code = $request->get('code');
        $accountAction->save();

        return $accountAction;
    }

    public function update(Request $request, int $id, int $actionId)
    {
        $account = $this->resolveAccount($id);

        $request->validate([
            'key' => ['alpha_dash', new NoUppercase],
            'code' => ['alpha_num', new NoUppercase]
        ]);

        $accountAction = $account
                                ->actions()
                                ->where('id', $actionId)
                                ->firstOrFail();
        $accountAction->key = $request->get('key');
        $accountAction->code = $request->get('code');
        $accountAction->save();

        return $accountAction;
    }

    public function destroy(int $id, int $actionId)
    {
        return $this->resolveAccount($id)
                                 ->actions()
                                 ->where('id', $actionId)
                                 ->delete();
    }

    private function resolveAccount(int $id)
    {
        $account = Account::findOrFail($id);
        if ($account->dtmf_protocol == null) abort(403, 'DTMF Protocol must be configured');

        return $account;
    }
}
