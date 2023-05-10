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
use App\Alias;
use App\ExternalAccount;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Rules\BlacklistedUsername;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;
use App\Rules\WithoutSpaces;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request, $search = '')
    {
        $accounts = Account::orderBy('creation_time', 'desc')->with('externalAccount');

        if (!empty($search)) {
            $accounts = $accounts->where('username', 'like', '%'.$search.'%');
        }

        return view('admin.account.index', [
            'search' => $search,
            'accounts' => $accounts->paginate(30)->appends($request->query())
        ]);
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
        $request->validate([
            'username' => [
                'required',
                new NoUppercase,
                new IsNotPhoneNumber,
                new BlacklistedUsername,
                new SIPUsername,
                Rule::unique('accounts', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', $this->resolveDomain($request));
                }),
                'filled',
            ],
            'dtmf_protocol' => 'nullable|in:' . Account::dtmfProtocolsRule(),
            'email' => [
                'nullable',
                'email',
                config('app.account_email_unique') ? Rule::unique('accounts', 'email') : null
            ],
            'phone' => [
                'nullable',
                'unique:aliases,alias',
                'unique:accounts,username',
                new WithoutSpaces, 'starts_with:+'
            ]
        ]);

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->display_name = $request->get('display_name');
        $account->domain = $this->resolveDomain($request);
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');
        $account->dtmf_protocol = $request->get('dtmf_protocol');
        $account->save();

        $this->fillPassword($request, $account);
        $this->fillPhone($request, $account);

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
            'username' => [
                'required',
                new NoUppercase,
                new IsNotPhoneNumber,
                new BlacklistedUsername,
                new SIPUsername,
                Rule::unique('accounts', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', $this->resolveDomain($request));
                })->ignore($id),
                'filled',
            ],
            'dtmf_protocol' => 'nullable|in:' . Account::dtmfProtocolsRule(),
            'email' => [
                'nullable',
                'email',
                config('app.account_email_unique') ? Rule::unique('accounts', 'email')->ignore($id) : null
            ],
            'phone' => [
                'nullable',
                'unique:aliases,alias',
                'unique:accounts,username',
                new WithoutSpaces, 'starts_with:+'
            ]
        ]);

        $account = Account::findOrFail($id);
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->display_name = $request->get('display_name');
        $account->dtmf_protocol = $request->get('dtmf_protocol');
        $account->save();

        $this->fillPassword($request, $account);
        $this->fillPhone($request, $account);

        Log::channel('events')->info('Web Admin: Account updated', ['id' => $account->identifier]);

        return redirect()->route('admin.account.show', $id);
    }

    public function search(Request $request)
    {
        return redirect()->route('admin.account.index', $request->get('search'));
    }

    public function attachExternalAccount(int $id)
    {
        $account = Account::findOrFail($id);
        $account->attachExternalAccount();

        Log::channel('events')->info('Web Admin: ExternalAccount attached', ['id' => $account->identifier]);

        return redirect()->back();
    }

    public function activate(int $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = true;
        $account->save();

        Log::channel('events')->info('Web Admin: Account activated', ['id' => $account->identifier]);

        return redirect()->back();
    }

    public function deactivate(int $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = false;
        $account->save();

        Log::channel('events')->info('Web Admin: Account deactivated', ['id' => $account->identifier]);

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

    public function admin(int $id)
    {
        $account = Account::findOrFail($id);

        $admin = new Admin;
        $admin->account_id = $account->id;
        $admin->save();

        Log::channel('events')->info('Web Admin: Account set as admin', ['id' => $account->identifier]);

        return redirect()->back();
    }

    public function unadmin(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        // An admin cannot remove it's own permission
        if ($account->id == $request->user()->id) abort(403);

        if ($account->admin) $account->admin->delete();

        Log::channel('events')->info('Web Admin: Account unset as admin', ['id' => $account->identifier]);

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

    private function fillPassword(Request $request, Account $account)
    {
        if ($request->filled('password')) {
            $algorithm = $request->has('password_sha256') ? 'SHA-256' : 'MD5';
            $account->updatePassword($request->get('password'), $algorithm);
        }
    }

    private function fillPhone(Request $request, Account $account)
    {
        if ($request->filled('phone')) {
            $account->alias()->delete();

            $alias = new Alias;
            $alias->alias = $request->get('phone');
            $alias->domain = config('app.sip_domain');
            $alias->account_id = $account->id;
            $alias->save();
        }
    }
}
