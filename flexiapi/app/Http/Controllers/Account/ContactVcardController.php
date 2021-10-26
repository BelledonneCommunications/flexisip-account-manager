<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2021 Belledonne Communications SARL, All rights reserved.

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

class ContactVcardController extends Controller
{
    public function index(Request $request)
    {
        return response(
            $request->user()->contacts->map(function ($contact) {
                return $contact->toVcard4();
            })->implode("\n")
        );
    }

    public function show(Request $request, string $sip)
    {
        return $request->user()
            ->contacts()
            ->sip($sip)
            ->firstOrFail()
            ->toVcard4();
    }
}
