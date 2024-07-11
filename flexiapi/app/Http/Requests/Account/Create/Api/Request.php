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

namespace App\Http\Requests\Account\Create\Api;

use App\Http\Requests\Account\Create\Request as CreateRequest;
use App\Http\Requests\Api as RequestsApi;
use App\Rules\AccountCreationToken;
use App\Rules\AccountCreationTokenNotExpired;

class Request extends CreateRequest
{
    use RequestsApi;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules['account_creation_token'] = ['required', new AccountCreationToken, new AccountCreationTokenNotExpired];

        return $rules;
    }
}
