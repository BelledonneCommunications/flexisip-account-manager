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

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExternalAccount\CreateUpdate;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\ExternalAccount;
use App\Account;

class ExternalAccountController extends Controller
{
    public function show(int $accountId)
    {
        return Account::findOrFail($accountId)->external()->firstOrFail();
    }

    public function store(CreateUpdate $request, int $accountId)
    {
        return (new AccountService)->storeExternalAccount($request, $accountId);
    }

    public function destroy(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        return $account->external->delete();
    }
}
