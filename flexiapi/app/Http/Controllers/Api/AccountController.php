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

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmedRegistration;
use App\Helpers\Utils;
use App\Account;
use App\Password;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        return Account::where('id', $request->user()->id)
                      ->without(['api_key', 'email_changed.new_email'])
                      ->first();
    }

    public function requestEmailUpdate(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', Rule::notIn([$request->user()->email])],
        ]);
        $request->user()->requestEmailUpdate($request->get('email'));
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required',
        ]);

        $account = $request->user();
        $account->activated = true;
        $account->save();

        $algorithm = $request->get('algorithm');

        if ($account->passwords()->count() > 0) {
            $request->validate(['old_password' => 'required']);

            foreach ($account->passwords as $password) {
                if (hash_equals(
                    $password->password,
                    Utils::bchash($account->username, $account->domain, $request->get('old_password'), $password->algorithm)
                )) {
                    $account->updatePassword($request->get('password'), $algorithm);
                    return response()->json();
                }
            }

            return response()->json(['errors' => ['old_password' => 'Incorrect old password']], 422);
        } else {
            $account->updatePassword($request->get('password'), $algorithm);

            if (!empty($account->email)) {
                Mail::to($account)->send(new ConfirmedRegistration($account));
            }
        }
    }
}
