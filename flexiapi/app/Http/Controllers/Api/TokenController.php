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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Token;
use App\Libraries\FlexisipPusherConnector;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;

class TokenController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'pn_provider' => 'required',
            'pn_param' => 'required',
            'pn_prid' => 'required',
        ]);

        if (Token::where('pn_provider', $request->get('pn_provider'))
                 ->where('pn_param', $request->get('pn_param'))
                 ->where('pn_prid', $request->get('pn_prid'))
                 ->where('used', false)
                 ->count() > 0) {
            abort(403, 'A similar token was already used');
        }

        if (Token::where('pn_provider', $request->get('pn_provider'))
                 ->where('pn_param', $request->get('pn_param'))
                 ->where('pn_prid', $request->get('pn_prid'))
                 ->count() > 3) {
            abort(403, 'The limit of tokens generated for this device has been reached');
        }

        $token = new Token;
        $token->token = Str::random(WebAuthenticateController::$emailCodeSize);
        $token->pn_provider = $request->get('pn_provider');
        $token->pn_param = $request->get('pn_param');
        $token->pn_prid = $request->get('pn_prid');

        // Send the token to the device via Push Notification
        $fp = new FlexisipPusherConnector($token->pn_provider, $token->pn_param, $token->pn_prid);
        if ($fp->sendToken($token->token)) {
            Log::channel('events')->info('API: Token sent', ['token' => $token->token]);

            $token->save();
        } else {
            abort(503, "Token not sent");
        }
    }
}
