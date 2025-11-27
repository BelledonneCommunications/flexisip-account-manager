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

namespace App\Http\Controllers\Api\Admin\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request, int $accountId)
    {
        return $request->space->accounts()->findOrFail($accountId)->contacts;
    }

    public function show(Request $request, int $accountId, int $contactId)
    {
        return $request->space->accounts()->findOrFail($accountId)
                      ->contacts()
                      ->where('id', $contactId)
                      ->firstOrFail();
    }

    public function add(Request $request, int $accountId, int $contactId)
    {
        $account = $request->space->accounts()->findOrFail($accountId);
        $account->contacts()->detach($contactId);

        if ($request->space->accounts()->findOrFail($contactId)) {
            return $account->contacts()->attach($contactId);
        }
    }

    public function remove(Request $request, int $accountId, int $contactId)
    {
        $account = $request->space->accounts()->findOrFail($accountId);

        if (!$account->contacts()->pluck('id')->contains($contactId)) {
            abort(404);
        }

        return $account->contacts()->detach($contactId);
    }
}
