<?php

namespace App\Http\Controllers\Account;

use App\ResetPasswordEmailToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResetPasswordEmailController extends Controller
{
    public function change(string $token)
    {
        $token = ResetPasswordEmailToken::where('token', $token)->firstOrFail();

        return view('account.password_reset', [
            'token' => $token
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|size:16',
            'password' => 'required|min:8|confirmed',
            'h-captcha-response'  => captchaConfigured() ? 'required|HCaptcha' : ''
        ]);

        $token = ResetPasswordEmailToken::where('token', $request->get('token'))->firstOrFail();

        if ($token->offed()) abort(403);

        $token->account->updatePassword($request->get('password'));
        $token->consume();

        return view('account.password_changed');
    }
}
