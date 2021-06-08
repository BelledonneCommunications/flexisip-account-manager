<?php

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
