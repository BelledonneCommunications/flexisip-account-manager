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

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\AuthToken;
use Illuminate\Http\Request;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;

use Illuminate\Support\Facades\Auth;

class AuthTokenController extends Controller
{
    public function qrcode(string $token)
    {
        $authToken = AuthToken::where('token', $token)
            ->valid()
            ->firstOrFail();

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data(
                $authToken->account_id
                ? route('auth_tokens.auth', ['token' => $authToken->token])
                : route('account.auth_tokens.auth.external', ['token' => $authToken->token])
            )
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->build();

        return response($result->getString())->header('Content-Type', $result->getMimeType());
    }
    /**
     * @desc Authenticate a user on a new device from a token generated from an authenticated account
     */

    public function create(Request $request)
    {
        $request->user()->generateAuthToken();

        return redirect()->back();
    }

    public function auth(Request $request, string $token)
    {
        $authToken = AuthToken::where('token', $token)->valid()->firstOrFail();

        Auth::login($authToken->account);

        $authToken->delete();

        return redirect()->route('account.dashboard');
    }

    /**
     * @desc Assign an authenticated account to an auth token generated from an external user
     */
    public function authExternal(Request $request, string $token)
    {
        $authToken = AuthToken::where('token', $token)->valid()->firstOrFail();

        if (!$authToken->account_id) {
            $authToken->account_id = $request->user()->id;
            $authToken->save();
        }

        return redirect()->route('account.dashboard');
    }
}
