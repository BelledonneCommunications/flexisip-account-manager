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

use App\Account;
use App\ContactsList;
use App\Http\Requests\Account\Create\Web\AsAdminRequest;
use App\Http\Requests\Account\Update\Web\AsAdminRequest as WebAsAdminRequest;
use App\Libraries\FlexisipRedisConnector;
use App\Services\AccountService;
use App\Space;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'order_by' => 'in:username,updated_at',
            'order_sort' => 'in:asc,desc',
        ]);

        $accounts = Account::orderBy($request->get('order_by', 'updated_at'), $request->get('order_sort', 'desc'));

        if ($request->has('search')) {
            $accounts = $accounts->where('username', 'like', '%' . $request->get('search') . '%');
        }

        if ($request->has('updated_date')) {
            $accounts->whereDate('updated_at', $request->get('updated_date'));
        }

        if ($request->has('contacts_list')) {
            $accounts->whereHas('contactsLists', function ($query) use ($request) {
                $query->where('id', $request->get('contacts_list'));
            });
        }

        if ($request->has('domain')) {
            $accounts->where('domain', $request->get('domain'));
        }

        return view('admin.account.index', [
            'space' => (!$request->user()->superAdmin)
                ? $request->user()->space
                : null,
            'domains' => Account::groupBy('domain')->pluck('domain'),
            'contacts_lists' => ContactsList::all()->pluck('title', 'id'),
            'accounts' => $accounts->paginate(20)->appends($request->query()),
        ]);
    }

    public function search(Request $request)
    {
        return redirect()->route('admin.account.index', $request->except('_token', 'query'));
    }

    public function show(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.show', [
            'account' => $account,
            'devices' => (new FlexisipRedisConnector)->getDevices($account->identifier)
        ]);
    }

    public function create(Request $request)
    {
        $account = new Account;

        if ($request->has('admin')) {
            $account->admin = true;
        }

        if ($request->has('domain')) {
            $account->domain = $request->get('domain');
        }

        return view('admin.account.create_edit', [
            'account' => $account,
            'domains' => $request->user()?->superAdmin
                ? Space::notFull()->get()
                : Space::where('domain', $request->user()->domain)->get(),
            'protocols' => [null => 'None'] + Account::$dtmfProtocols
        ]);
    }

    public function store(AsAdminRequest $request)
    {
        $account = (new AccountService)->store($request);

        Log::channel('events')->info('Web Admin: Account created', ['id' => $account->identifier]);

        return redirect()->route('admin.account.show', $account);
    }

    public function edit(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.create_edit', [
            'account' => $account,
            'protocols' => [null => __('Empty')] + Account::$dtmfProtocols,
            'domains' => $request->user()?->superAdmin
                ? Space::all()
                : Space::where('domain', $account->domain)->get(),
        ]);
    }

    public function update(WebAsAdminRequest $request, int $accountId)
    {
        $account = (new AccountService)->update($request, $accountId);

        Log::channel('events')->info('Web Admin: Account updated', ['id' => $account->identifier]);

        return redirect()->route('admin.account.show', $accountId);
    }

    public function provision(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->provision();
        $account->save();

        Log::channel('events')->info('Web Admin: Account provisioned', ['id' => $account->identifier]);

        return redirect()->back()->withFragment('provisioning');
    }

    public function delete(int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.delete', [
            'account' => $account
        ]);
    }

    public function destroy(Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        (new AccountService)->destroy($request, $request->get('account_id'));

        Log::channel('events')->info('Web Admin: Account deleted', ['id' => $account->identifier]);

        return redirect()->route('admin.account.index');
    }

    public function contactsListAdd(Request $request, int $accountId)
    {
        $request->validate([
            'contacts_list_id' => 'required|exists:contacts_lists,id'
        ]);

        $account = Account::findOrFail($accountId);
        $account->contactsLists()->detach([$request->get('contacts_list_id')]);
        $account->contactsLists()->attach([$request->get('contacts_list_id')]);

        return redirect()->route('admin.account.contact.index', $accountId)->withFragment('#contacts_lists');
    }

    public function contactsListRemove(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->contactsLists()->detach([$request->get('contacts_list_id')]);

        return redirect()->route('admin.account.contact.index', $accountId)->withFragment('#contacts_lists');
    }
}
