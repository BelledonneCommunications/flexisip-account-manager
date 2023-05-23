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

namespace App\Rules;

use App\AccountCreationToken as AppAccountCreationToken;
use App\Http\Controllers\Account\AuthenticateController;
use Illuminate\Contracts\Validation\Rule;

class AccountCreationToken implements Rule
{
    public function passes($attribute, $value)
    {
        return AppAccountCreationToken::where('token', $value)->where('used', false)->exists()
            && strlen($value) == AuthenticateController::$emailCodeSize;
    }

    public function message()
    {
        return 'Please provide a valid account_creation_token';
    }
}
