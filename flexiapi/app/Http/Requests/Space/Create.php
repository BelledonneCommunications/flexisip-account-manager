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

namespace App\Http\Requests\Space;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\EmailServer;
use App\Rules\Domain;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|unique:spaces',
            'domain' => ['required', 'unique:spaces', new Domain()],
            'account_realm' => ['nullable', new Domain()],
        ];
    }
}
