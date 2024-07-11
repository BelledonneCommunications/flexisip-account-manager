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

namespace App\Http\Requests\Account\Update\Api;

use App\Http\Requests\Account\Update\Request as UpdateRequest;
use App\Http\Requests\Api as RequestsApi;
use App\Http\Requests\AsAdmin;
use App\Rules\IsNotPhoneNumber;
use App\Rules\PasswordAlgorithm;

class AsAdminRequest extends UpdateRequest
{
    use RequestsApi, AsAdmin;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules['algorithm'] = ['required', new PasswordAlgorithm()];
        $rules['admin'] = 'boolean|nullable';
        $rules['activated'] = 'boolean|nullable';

        if (config('app.allow_phone_number_username_admin_api') == true) {
            array_splice(
                $rules['username'],
                array_search(new IsNotPhoneNumber(), $rules['username']),
                1
            );
        }

        return $rules;
    }
}
