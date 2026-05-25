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

namespace App\Http\Requests\Account\Update;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Account;
use App\Rules\BlacklistedUsername;
use App\Rules\FilteredPhone;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;

class Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $domain = space()->domain;
        $accountId = $this->route('account_id');

        return [
            'username' => [
                'required',
                new NoUppercase,
                new IsNotPhoneNumber,
                new BlacklistedUsername,
                new SIPUsername,
                Rule::unique('accounts', 'username')->where(function ($query) use ($domain) {
                    $query->where('domain', $domain);
                })->ignore($accountId, 'id'),
                'filled',
            ],
            'domain' => 'exists:spaces,domain',
            'email' => space()->unique_email
                ? [
                    'nullable',
                    'email',
                    Rule::unique('accounts', 'email')->where(function ($query) use ($domain) {
                        $query->where('domain', $domain);
                    })->ignore($this->route('id'))
                ]
                : ['nullable', 'email'],
            'role' => 'in:admin,end_user',
            'dtmf_protocol' => 'nullable|in:' . Account::dtmfProtocolsRule(),
            'phone' => [
                'nullable',
                'phone',
                new FilteredPhone,
                Rule::unique('accounts', 'username')->where(function ($query) use ($domain) {
                    $query->where('domain', $domain);
                })->ignore($accountId, 'id'),
                Rule::unique('accounts', 'phone')->where(function ($query) use ($domain) {
                    $query->where('domain', $domain);
                })->ignore($accountId, 'id'),
            ]
        ];
    }
}
