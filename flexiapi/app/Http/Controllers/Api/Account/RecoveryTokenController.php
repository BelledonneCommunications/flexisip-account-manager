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

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\AccountRecoveryToken;
use App\Rules\PnParam;
use App\Rules\PnPrid;
use App\Rules\PnProvider;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Libraries\FlexisipPusherConnector;

class RecoveryTokenController extends Controller
{
    public function sendByPush(Request $request)
    {
        $request->validate([
            'pn_provider' => ['required', new PnProvider],
            'pn_param' => [new PnParam],
            'pn_prid' => [new PnPrid],
        ]);

        $last = AccountRecoveryToken::where('pn_provider', $request->get('pn_provider'))
            ->where('pn_param', $request->get('pn_param'))
            ->where('pn_prid', $request->get('pn_prid'))
            ->where('created_at', '>=', Carbon::now()->subMinutes(config('app.account_recovery_token_retry_minutes'))->toDateTimeString())
            ->where('used', true)
            ->latest()
            ->first();

        if ($last) {
            Log::info('API: Token throttled', ['token' => $last->token]);
            abort(429, 'Last token requested too recently');
        }

        $token = new AccountRecoveryToken;
        $token->token = Str::random(WebAuthenticateController::$emailCodeSize);
        $token->pn_provider = $request->get('pn_provider');
        $token->pn_param = $request->get('pn_param');
        $token->pn_prid = $request->get('pn_prid');
        $token->fillRequestInfo($request);

        $fp = new FlexisipPusherConnector($token->pn_provider, $token->pn_param, $token->pn_prid);
        if ($fp->sendToken($token->token)) {
            Log::info('API: AccountRecoveryToken sent', ['token' => $token->token]);

            $token->save();
            return;
        }

        abort(503, "Token not sent");
    }
}
