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

namespace App\Http\Controllers\Api\Account;

use App\AuthToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthTokenController extends Controller
{
    public function store(Request $request)
    {
        $authToken = new AuthToken;
        $authToken->token = Str::random(32);
        $authToken->fillRequestInfo($request);
        $authToken->save();

        return $authToken;
    }

    public function attach(Request $request, string $token)
    {
        $authToken = AuthToken::where('token', $token)->valid()->firstOrFail();

        if (!$authToken->account_id) {
            $authToken->account_id = $request->user()->id;
            $authToken->save();

            return;
        }

        abort(404);
    }
}
