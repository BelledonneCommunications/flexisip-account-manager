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

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Account;
use App\AccountTombstone;
use App\Helpers\Utils;

class AccountController extends Controller
{
    public function home(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('account.panel');
        }

        return view('account.home', [
            'count' => Account::where('activated', true)->count()
        ]);
    }

    public function documentation(Request $request)
    {
        return view('account.documentation', [
            'documentation' => Utils::markdownDocumentationView('account.documentation_markdown')
        ]);
    }

    public function panel(Request $request)
    {
        return view('account.panel', [
            'account' => $request->user()
        ]);
    }

    public function generateApiKey(Request $request)
    {
        $account = $request->user();
        $account->generateApiKey();

        return redirect()->back();
    }

    public function delete(Request $request)
    {
        return view('account.delete', [
            'account' => $request->user()
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['identifier' => 'required|same:identifier_confirm']);

        if (!$request->user()->hasTombstone()) {
            $tombstone = new AccountTombstone;
            $tombstone->username = $request->user()->username;
            $tombstone->domain = $request->user()->domain;
            $tombstone->save();
        }

        $request->user()->delete();
        Auth::logout();

        return redirect()->route('account.login');
    }
}
