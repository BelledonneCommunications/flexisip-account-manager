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
use Illuminate\Support\Facades\Mail;

use App\Account;
use App\Password;
use App\Helpers\Utils;
use App\Mail\ConfirmedRegistration;

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

        $algorithm = $request->has('password_sha256') ? 'SHA-256' : 'MD5';

        if ($account->passwords()->count() > 0) {
            $request->validate(['old_password' => 'required']);

            foreach ($account->passwords as $password) {
                // If one of the password stored equals the one entered
                if (hash_equals(
                    $password->password,
                    Utils::bchash($account->username, $account->domain, $request->get('old_password'), $password->algorithm)
                )) {
                    $account->updatePassword($request->get('password'), $algorithm);
                    $request->session()->flash('success', 'Password successfully changed');
                    return redirect()->route('account.panel');
                }
            }

            return redirect()->back()->withErrors(['old_password' => 'Old password not correct']);
        } else {
            // No password yet
            $account->updatePassword($request->get('password'), $algorithm);

            if (!empty($account->email)) {
                Mail::to($account)->send(new ConfirmedRegistration($account));
            }

            $request->session()->flash('success', 'Password successfully set. Your SIP account creation process is now finished.');

            return redirect()->route('account.panel');
        }
    }
}
