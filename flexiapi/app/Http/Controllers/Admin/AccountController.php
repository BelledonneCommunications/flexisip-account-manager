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

use App\Account;
use App\Admin;

class AccountController extends Controller
{
    public function index(Request $request, $search = '')
    {
        $accounts = Account::orderBy('creation_time', 'desc');

        if (!empty($search)) {
            $accounts = $accounts->where('username', 'like', '%'.$search.'%');
        }

        return view('admin.account.index', [
            'search' => $search,
            'accounts' => $accounts->paginate(30)->appends($request->query())
        ]);
    }

    public function search(Request $request)
    {
        return redirect()->route('admin.account.index', $request->get('search'));
    }

    public function show(Request $request, $id)
    {
        return view('admin.account.show', [
            'account' => Account::findOrFail($id)
        ]);
    }

    public function activate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = true;
        $account->save();

        return redirect()->back();
    }

    public function deactivate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = false;
        $account->save();

        return redirect()->back();
    }

    public function admin(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $admin = new Admin;
        $admin->account_id = $account->id;
        $admin->save();

        return redirect()->back();
    }

    public function unadmin(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        // An admin cannot remove it's own permission
        if ($account->id == $request->user()->id) abort(403);

        if ($account->admin) $account->admin->delete();

        return redirect()->back();
    }

    public function generateApiKey(Request $request)
    {
        $account = $request->user();
        $account->generateApiKey();

        return redirect()->back();
    }
}
