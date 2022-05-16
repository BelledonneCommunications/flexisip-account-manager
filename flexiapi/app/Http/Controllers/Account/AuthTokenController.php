<?php

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
                : route('auth_tokens.auth.external', ['token' => $authToken->token])
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

        $request->session()->flash('success', 'Successfully authenticated');

        return redirect()->route('account.panel');
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

            $request->session()->flash('success', 'External device successfully authenticated');
        }

        return redirect()->route('account.panel');
    }
}
