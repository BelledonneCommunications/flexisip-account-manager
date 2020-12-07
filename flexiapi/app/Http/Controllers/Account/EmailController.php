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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Controller;
use App\Mail\ChangingEmail;
use App\Mail\ChangedEmail;
use App\EmailChanged;

class EmailController extends Controller
{
    public function show(Request $request)
    {
        return view('account.email', [
            'account' => $request->user()
        ]);
    }

    public function requestUpdate(Request $request)
    {
        $request->validate([
            'email_current' => ['required', Rule::in([$request->user()->email])],
            'email' => 'required|different:email_current|confirmed|email',
        ]);

        $request->user()->requestEmailUpdate($request->get('email'));

        $request->session()->flash('success', 'An email was sent with a confirmation link. Please click it to update your email address.');
        return redirect()->route('account.panel');
    }

    public function update(Request $request, string $hash)
    {
        $account = $request->user();

        if ($account->emailChanged && $account->emailChanged->hash == $hash) {
            $account->email = $account->emailChanged->new_email;
            $account->save();

            Mail::to($account)->send(new ChangedEmail());

            $account->emailChanged->delete();

            $request->session()->flash('success', 'Email successfully updated');
            return redirect()->route('account.panel');
        }

        abort(404);
    }
}
