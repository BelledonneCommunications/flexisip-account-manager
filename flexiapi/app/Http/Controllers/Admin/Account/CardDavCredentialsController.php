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

namespace App\Http\Controllers\Admin\Account;

use App\Account;
use App\AccountCardDavCredentials;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\Account\CardDavCredentials;

class CardDavCredentialsController extends Controller
{
    public function create(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $this->checkFeatureEnabled($account);

        return view('admin.account.carddav.create', [
            'account' => $account,
            'carddavServers' => $account->remainingCardDavCredentialsCreatable
        ]);
    }

    public function store(CardDavCredentials $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $this->checkFeatureEnabled($account);

        $request->validate([
            'carddav_id' => ['required', Rule::exists('space_carddav_servers', 'id')->where(function (Builder $query) use ($account) {
                return $query->where('space_id', $account->space->id);
            })]
        ]);

        $accountCarddavCredentials = new AccountCardDavCredentials;
        $accountCarddavCredentials->space_carddav_server_id = $request->get('carddav_id');
        $accountCarddavCredentials->account_id = $account->id;
        $accountCarddavCredentials->username = $request->get('username');
        $accountCarddavCredentials->realm = $request->get('realm');
        $accountCarddavCredentials->password = bchash(
            $request->get('username'),
            $request->get('realm'),
            $request->get('password'),
            $request->get('algorithm')
        );
        $accountCarddavCredentials->algorithm = $request->get('algorithm');
        $accountCarddavCredentials->save();

        return redirect()->route('admin.account.show', $account);
    }

    public function delete(int $accountId, int $cardDavId)
    {
        $account = Account::findOrFail($accountId);
        $this->checkFeatureEnabled($account);

        $accountCarddavCredentials = AccountCardDavCredentials::where('space_carddav_server_id', $cardDavId)
            ->where('account_id', $account->id)
            ->firstOrFail();

        return view('admin.account.carddav.delete', [
            'account' => $account,
            'carddavCredentials' => $accountCarddavCredentials,
        ]);
    }

    public function destroy(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $this->checkFeatureEnabled($account);

        $accountCarddavCredentials = AccountCardDavCredentials::where('space_carddav_server_id', $request->carddav_id)
            ->where('account_id', $account->id)
            ->delete();

        return redirect()->route('admin.account.show', $account);
    }

    private function checkFeatureEnabled(Account $account)
    {
        if (!$account->space->carddav_user_credentials) {
            abort(403, 'CardDav Credentials features disabled');
        }
    }
}
