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
use App\Http\Requests\ExternalAccount\CreateUpdate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\ExternalAccount;
use App\Account;

class ExternalAccountController extends Controller
{
    public function show(int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.external.show', [
            'account' => $account,
            'externalAccount' => $account->external ?? new ExternalAccount,
            'protocols' => ExternalAccount::PROTOCOLS
        ]);
    }

    public function store(CreateUpdate $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $externalAccount = $account->external ?? new ExternalAccount;

        $password = '';
        if ($account->external?->realm != $request->get('realm')) {
            $password = 'required_with:realm';
        } elseif ($externalAccount->password == null) {
            $password = 'required';
        }

        $request->validate(['password' => $password]);

        $algorithm = 'MD5';

        $externalAccount->account_id = $account->id;
        $externalAccount->username = $request->get('username');
        $externalAccount->domain = $request->get('domain');
        $externalAccount->realm = $request->get('realm');
        $externalAccount->registrar = $request->get('registrar');
        $externalAccount->outbound_proxy = $request->get('outbound_proxy');
        $externalAccount->protocol = $request->get('protocol');

        if (!empty($request->get('password'))) {
            $externalAccount->password = bchash(
                $externalAccount->username,
                $externalAccount->realm ?? $externalAccount->domain,
                $request->get('password'),
                $algorithm
            );
            $externalAccount->algorithm = $algorithm;
        }

        $externalAccount->save();

        return redirect()->route('admin.account.external.show', $account->id);
    }

    public function delete(int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.external.delete', [
            'account' => $account
        ]);
    }

    public function destroy(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->external->delete();

        return redirect()->route('admin.account.external.show', $account->id);
    }
}
