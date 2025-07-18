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

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PasswordController extends Controller
{
    public function show(Request $request)
    {
        return view('account.password', [
            'account' => $request->user()
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|filled',
        ]);

        $account = $request->user();
        $account->activated = true;
        $account->save();

        $account->updatePassword($request->get('password'));

        if ($account->passwords()->count() > 0) {
            Log::channel('events')->info('Web: Password changed', ['id' => $account->identifier]);

            return redirect()->route('account.dashboard');
        }

        Log::channel('events')->info('Web: Password set for the first time', ['id' => $account->identifier]);

        return redirect()->route('account.dashboard');
    }
}
