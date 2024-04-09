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
use Illuminate\Support\Facades\Cookie;

class ApiKeyController extends Controller
{
    public function generate(Request $request)
    {
        $account = $request->user();
        $account->generateApiKey($request);

        $account->refresh();
        Cookie::queue('x-api-key', $account->apiKey->key, config('app.api_key_expiration_minutes'));

        return $account->apiKey->key;
    }

    public function generateFromToken(Request $request, string $token)
    {
        $authToken = AuthToken::where('token', $token)->valid()->firstOrFail();

        if ($authToken->account) {
            $authToken->account->generateApiKey($request);

            $authToken->account->refresh();
            Cookie::queue('x-api-key', $authToken->account->apiKey->key, config('app.api_key_expiration_minutes'));

            $apiKey = $authToken->account->apiKey->key;
            $authToken->delete();

            return response()->json(['api_key' => $apiKey]);
        }

        abort(404);
    }
}
