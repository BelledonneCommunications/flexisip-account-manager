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

namespace App\Http\Requests\ExternalAccount;

use App\Rules\DomainOrIp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\ExternalAccount;
use App\Rules\SIPUsername;

class CreateUpdate extends FormRequest
{
    public function rules()
    {
        $usernameValidation = Rule::unique('external_accounts')->where(function ($query) {
            return $query->where('username', $this->username)->where('domain', $this->domain);
        });

        if ($this->method() == 'POST') {
            $usernameValidation = $usernameValidation->ignore($this->route('account'), 'account_id');
        }

        return [
            'username' => ['required', $usernameValidation, new SIPUsername()],
            'domain' => ['required', new DomainOrIp()],
            'realm' => 'different:domain',
            'registrar' => 'different:domain',
            'outbound_proxy' => 'different:domain',
            'protocol' => [
                'required',
                Rule::in(ExternalAccount::PROTOCOLS),
            ]
        ];
    }
}
