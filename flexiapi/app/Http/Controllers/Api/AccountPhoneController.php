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
use App\Http\Controllers\Controller;

use App\Rules\WithoutSpaces;
use App\Helpers\Utils;
use App\Libraries\OvhSMS;

use App\PhoneChangeCode;
use App\Alias;

class AccountPhoneController extends Controller
{
    public function requestUpdate(Request $request)
    {
        $request->validate([
            'phone' => [
                'required', 'unique:aliases,alias',
                'unique:accounts,username',
                new WithoutSpaces, 'starts_with:+'
            ]
        ]);

        $account = $request->user();

        $phoneChangeCode = $account->phoneChangeCode ?? new PhoneChangeCode;
        $phoneChangeCode->account_id = $account->id;
        $phoneChangeCode->phone = $request->get('phone');
        $phoneChangeCode->code = Utils::generatePin();
        $phoneChangeCode->save();

        $ovhSMS = new OvhSMS;
        $ovhSMS->send($request->get('phone'), 'Your ' . config('app.name') . ' validation code is ' . $phoneChangeCode->code);
    }

    public function update(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:4'
        ]);

        $account = $request->user();

        $phoneChangeCode = $account->phoneChangeCode()->firstOrFail();
        if ($phoneChangeCode->code == $request->get('code')) {
            $account->alias()->delete();

            $alias = new Alias;
            $alias->alias = $phoneChangeCode->phone;
            $alias->domain = config('app.sip_domain');
            $alias->account_id = $account->id;
            $alias->save();

            $phoneChangeCode->delete();

            $account->refresh();

            return $account;
        }

        $phoneChangeCode->delete();
        abort(403);
    }
}
