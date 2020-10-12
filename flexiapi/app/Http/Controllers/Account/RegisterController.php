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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

use App\Account;
use App\Alias;
use App\Rules\SIP;
use App\Rules\WithoutSpaces;
use App\Helpers\Utils;
use App\Libraries\OvhSMS;
use App\Mail\RegisterConfirmation;
use App\Mail\NewsletterRegistration;

class RegisterController extends Controller
{
    private $emailCodeSize = 13;

    public function register(Request $request)
    {
        if (config('app.phone_authentication') == false) {
            return redirect()->route('account.register.email');
        }

        return view('account.register');
    }

    public function registerPhone(Request $request)
    {
        return view('account.register.phone', [
            'domain' => '@' . config('app.sip_domain')
        ]);
    }

    public function registerEmail(Request $request)
    {
        return view('account.register.email', [
            'domain' => '@' . config('app.sip_domain')
        ]);
    }

    public function storeEmail(Request $request)
    {
        $request->validate([
            'terms' => 'accepted',
            'privacy' => 'accepted',
            'username' => [
                'required',
                Rule::unique('external.accounts', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', config('app.sip_domain'));
                }),
                'filled',
                new WithoutSpaces
            ],
            'g-recaptcha-response'  => 'required|captcha',
            'email' => 'required|email|confirmed'
        ]);

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->activated = false;
        $account->domain = config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');
        $account->save();

        $account->confirmation_key = Str::random($this->emailCodeSize);
        $account->save();

        if (!empty(config('app.newsletter_registration_address'))
         && $request->has('newsletter')) {
            Mail::to(config('app.newsletter_registration_address'))->send(new NewsletterRegistration($account));
        }

        Mail::to($account)->send(new RegisterConfirmation($account));

        return view('account.authenticate.email', [
            'account' => $account
        ]);
    }

    public function storePhone(Request $request)
    {
        $request->validate([
            'terms' =>'accepted',
            'privacy' => 'accepted',
            'username' => 'unique:external.accounts,username|nullable|filled',
            'phone' => [
                'required', 'unique:external.aliases,alias',
                'unique:external.accounts,username',
                new WithoutSpaces, 'starts_with:+', 'phone:AUTO'
            ],
            'g-recaptcha-response'  => 'required|captcha',
        ]);

        $account = new Account;
        $account->username = !empty($request->get('username'))
            ? $request->get('username')
            : $request->get('phone');

        $account->email = $request->get('email');
        $account->activated = false;
        $account->domain = config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');
        $account->save();

        $alias = new Alias;
        $alias->alias = $request->get('phone');
        $alias->domain = config('app.sip_domain');
        $alias->account_id = $account->id;
        $alias->save();

        $account->confirmation_key = Utils::generatePin();
        $account->save();

        $ovhSMS = new OvhSMS;
        $ovhSMS->send($request->get('phone'), 'Your '.config('app.name').' validation code is '.$account->confirmation_key);

        return view('account.authenticate.phone', [
            'account' => $account
        ]);
    }
}
