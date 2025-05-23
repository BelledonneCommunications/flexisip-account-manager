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
use App\Http\Controllers\Controller;
use App\Services\AccountService;
use App\Services\BlockingService;

class PhoneController extends Controller
{
    public function change(Request $request)
    {
        return view('account.phone.change', [
            'account' => $request->user()
        ]);
    }

    public function requestChange(Request $request)
    {
        $request->validate(['h-captcha-response' => captchaConfigured() ? 'required|HCaptcha': '']);

        if ((new BlockingService($request->user()))->checkBlock()) {
            return redirect()->route('account.blocked');
        }

        (new AccountService(api: false))->requestPhoneChange($request);

        return redirect()->route('account.phone.validate');
    }

    public function validateChange(Request $request)
    {
        return view('account.phone.validate', [
            'phoneChangeCode' => $request->user()->phoneChangeCode()->firstOrFail()
        ]);
    }

    public function store(Request $request)
    {
        if ((new AccountService(api: false))->updatePhone($request)) {
            return redirect()->route('account.dashboard');
        }

        return redirect()->route('account.phone.change')->withErrors([
            'code' => __('The code is not valid')
        ]);
    }
}
