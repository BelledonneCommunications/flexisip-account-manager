<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

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
use Carbon\Carbon;

use App\Account;
use App\Admin;
use App\ExternalAccount;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\UpdateAccountRequest;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::orderBy('updated_at', $request->get('updated_at_order', 'desc'))
            ->with('externalAccount');

        if ($request->has('search')) {
            $accounts = $accounts->where('username', 'like', '%' . $request->get('search') . '%');
        }

        if ($request->has('updated_date')) {
            $accounts->whereDate('updated_at', $request->get('updated_date'));
        }

        return view('admin.account.index', [
            'search' => $request->get('search'),
            'updated_date' => $request->get('updated_date'),
            'accounts' => $accounts->paginate(20)->appends($request->query()),
            'updated_at_order' => $request->get('updated_at_order') == 'desc' ? 'asc' : 'desc'
        ]);
    }

    public function search(Request $request)
    {
        return redirect()->route('admin.account.index', $request->except('_token'));
    }

    public function show(int $id)
    {
        return view('admin.account.show', [
            'external_accounts_count' => ExternalAccount::where('used', false)->count(),
            'account' => Account::findOrFail($id)
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.account.create_edit', [
            'account' => new Account,
            'protocols' => [null => 'None'] + Account::$dtmfProtocols
        ]);
    }

    public function store(CreateAccountRequest $request)
    {
        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->display_name = $request->get('display_name');
        $account->domain = resolveDomain($request);
        $account->ip_address = $request->ip();
        $account->created_at = Carbon::now();
        $account->user_agent = config('app.name');
        $account->dtmf_protocol = $request->get('dtmf_protocol');
        $account->activated = $request->has('activated');
        $account->save();

        $account->phone = $request->get('phone');
        $account->fillPassword($request);

        Log::channel('events')->info('Web Admin: Account created', ['id' => $account->identifier]);

        return redirect()->route('admin.account.show', $account->id);
    }

    public function edit(int $id)
    {
        return view('admin.account.create_edit', [
            'account' => Account::findOrFail($id),
            'protocols' => [null => 'None'] + Account::$dtmfProtocols
        ]);
    }

    public function update(UpdateAccountRequest $request, $id)
    {
        $request->validate([
            'password' => 'confirmed',
        ]);

        $account = Account::findOrFail($id);
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->display_name = $request->get('display_name');
        $account->dtmf_protocol = $request->get('dtmf_protocol');
        $account->activated = $request->has('activated');
        $account->save();

        $account->phone = $request->get('phone');
        $account->fillPassword($request);

        $account->setRole($request->get('role'));

        Log::channel('events')->info('Web Admin: Account updated', ['id' => $account->identifier]);

        return redirect()->route('admin.account.show', $id);
    }

    public function attachExternalAccount(int $id)
    {
        $account = Account::findOrFail($id);
        $account->attachExternalAccount();

        Log::channel('events')->info('Web Admin: ExternalAccount attached', ['id' => $account->identifier]);

        return redirect()->back();
    }

    public function provision(int $id)
    {
        $account = Account::findOrFail($id);
        $account->provision();
        $account->save();

        Log::channel('events')->info('Web Admin: Account provisioned', ['id' => $account->identifier]);

        return redirect()->back();
    }

    public function delete(int $id)
    {
        $account = Account::findOrFail($id);

        return view('admin.account.delete', [
            'account' => $account
        ]);
    }

    public function destroy(Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));
        $account->delete();

        $request->session()->flash('success', 'Account successfully destroyed');

        Log::channel('events')->info('Web Admin: Account deleted', ['id' => $account->identifier]);

        return redirect()->route('admin.account.index');
    }
}
