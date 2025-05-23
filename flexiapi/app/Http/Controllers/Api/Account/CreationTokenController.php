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

use App\AccountCreationToken;
use App\AccountCreationRequestToken;
use App\Rules\PnParam;
use App\Rules\PnPrid;
use App\Rules\PnProvider;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Libraries\FlexisipPusherConnector;
use App\Rules\AccountCreationRequestToken as RulesAccountCreationRequestToken;

class CreationTokenController extends Controller
{
    public function sendByPush(Request $request)
    {
        $request->validate([
            'pn_provider' => ['required', new PnProvider],
            'pn_param' => [new PnParam],
            'pn_prid' => [new PnPrid],
        ]);

        $last = AccountCreationToken::where('pn_provider', $request->get('pn_provider'))
            ->where('pn_param', $request->get('pn_param'))
            ->where('pn_prid', $request->get('pn_prid'))
            ->where('created_at', '>=', Carbon::now()->subMinutes(config('app.account_creation_token_retry_minutes'))->toDateTimeString())
            ->where('used', true)
            ->latest()
            ->first();

        if ($last) {
            Log::channel('events')->info('API: Token throttled', ['token' => $last->token]);
            abort(429, 'Last token requested too recently');
        }

        $token = new AccountCreationToken;
        $token->token = Str::random(WebAuthenticateController::$emailCodeSize);
        $token->pn_provider = $request->get('pn_provider');
        $token->pn_param = $request->get('pn_param');
        $token->pn_prid = $request->get('pn_prid');
        $token->fillRequestInfo($request);

        $fp = new FlexisipPusherConnector($token->pn_provider, $token->pn_param, $token->pn_prid);
        if ($fp->sendToken($token->token)) {
            Log::channel('events')->info('API: Account Creation Token sent', ['token' => $token->token]);

            $token->save();
            return;
        }

        abort(503, "Token not sent");
    }

    public function usingAccountRequestToken(Request $request)
    {
        $request->validate([
            'account_creation_request_token' => [
                'required',
                new RulesAccountCreationRequestToken
            ]
        ]);

        $creationRequestToken = AccountCreationRequestToken::where('token', $request->get('account_creation_request_token'))
            ->where('used', false)
            ->first();

        if ($creationRequestToken && $creationRequestToken->validated_at != null) {
            $accountCreationToken = new AccountCreationToken;
            $accountCreationToken->token = Str::random(WebAuthenticateController::$emailCodeSize);
            $accountCreationToken->fillRequestInfo($request);
            $accountCreationToken->save();

            $creationRequestToken->consume();
            $creationRequestToken->acc_creation_token_id = $accountCreationToken->id;
            $creationRequestToken->save();

            return $accountCreationToken;
        }

        return abort(404);
    }

    public function consume(Request $request)
    {
        $accountCreationToken = AccountCreationToken::where('token', $request->get('account_creation_token'))
            ->where('used', false)
            ->where('account_id', null)
            ->first();

        if ($accountCreationToken) {
            $accountCreationToken->account_id = $request->user()->id;
            $accountCreationToken->fillRequestInfo($request);
            $accountCreationToken->consume();

            return $accountCreationToken;
        }

        return abort(404);
    }
}
