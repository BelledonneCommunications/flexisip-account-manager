<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2023 Belledonne Communications SARL, All rights reserved.

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

use App\AccountCreationRequestToken;
use App\Http\Controllers\Controller;
use App\Rules\AccountCreationRequestToken as RulesAccountCreationRequestToken;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CreationRequestTokenController extends Controller
{
    public function check(Request $request, string $creationRequestToken)
    {
        $request->merge(['account_creation_request_token' => $creationRequestToken]);
        $request->validate([
            'account_creation_request_token' => [
                'required',
                new RulesAccountCreationRequestToken
            ]
        ]);

        $accountCreationRequestToken = AccountCreationRequestToken::where('token', $request->get('account_creation_request_token'))->firstOrFail();

        return view('account.creation_request_token.check', [
            'account_creation_request_token' => $accountCreationRequestToken
        ]);
    }

    public function validateToken(Request $request)
    {
        $request->validate([
            'account_creation_request_token' => [
                'required',
                new RulesAccountCreationRequestToken
            ],
            'g-recaptcha-response'  => captchaConfigured() ? 'required|captcha' : '',
        ]);

        $accountCreationRequestToken = AccountCreationRequestToken::where('token', $request->get('account_creation_request_token'))->firstOrFail();
        $accountCreationRequestToken->validated_at = Carbon::now();
        $accountCreationRequestToken->save();

        return view('account.creation_request_token.valid');
    }
}
