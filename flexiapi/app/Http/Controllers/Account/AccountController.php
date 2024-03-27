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
use App\Http\Requests\Account\Create\Web\Request as WebRequest;
use App\Services\AccountService;

class AccountController extends Controller
{
    public function documentation(Request $request)
    {
        return view('account.documentation', [
            'documentation' => markdownDocumentationView('account.documentation_markdown')
        ]);
    }

    public function blocked(Request $request)
    {
        return view('account.blocked');
    }

    public function panel(Request $request)
    {
        return view('account.dashboard', [
            'account' => $request->user()
        ]);
    }

    public function store(WebRequest $request)
    {
        $account = (new AccountService())->store($request);

        Auth::login($account);

        if ($request->has('phone')) {
            (new AccountService(api: false))->requestPhoneChange($request);
            return redirect()->route('account.phone.validate');
        } elseif ($request->has('email')) {
            (new AccountService(api: false))->requestEmailChange($request);
            return redirect()->route('account.email.validate');
        }

        return abort(404);
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

        $request->user()->createTombstone();

        (new AccountService)->destroy($request, $request->user()->id);

        Auth::logout();

        return redirect()->route('account.login');
    }
}
