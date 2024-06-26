<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

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

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use App\Services\BlockingService;

use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function requestUpdate(Request $request)
    {
        if ((new BlockingService($request->user()))->checkBlock()) {
            return abort(403, 'Account blocked');
        }

        if (!$request->user()->accountCreationToken?->consumed()) {
            return abort(403, 'Account unvalidated');
        }

        (new AccountService)->requestEmailChange($request);
    }

    public function update(Request $request)
    {
        return (new AccountService)->updateEmail($request);
    }
}
