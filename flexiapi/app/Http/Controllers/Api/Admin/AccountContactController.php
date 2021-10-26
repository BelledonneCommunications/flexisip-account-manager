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

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;

use App\Account;

class AccountContactController extends Controller
{
    public function index(int $id)
    {
        return Account::findOrFail($id)->contacts;
    }

    public function show(int $id, int $contactId)
    {
        return Account::findOrFail($id)
                      ->contacts()
                      ->where('id', $contactId)
                      ->firstOrFail();
    }

    public function add(int $id, int $contactId)
    {
        if (Account::findOrFail($id)->contacts()->pluck('id')->contains($contactId)) {
            abort(403);
        }

        if (Account::findOrFail($contactId)) {
            return Account::findOrFail($id)->contacts()->attach($contactId);
        }
    }

    public function remove(int $id, int $contactId)
    {
        if (!Account::findOrFail($id)->contacts()->pluck('id')->contains($contactId)) {
            abort(403);
        }

        return Account::findOrFail($id)->contacts()->detach($contactId);
    }
}
