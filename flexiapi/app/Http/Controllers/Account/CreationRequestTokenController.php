<?php

namespace App\Http\Controllers\Account;

use App\AccountCreationRequestToken;
use App\Http\Controllers\Controller;
use App\Rules\AccountCreationRequestToken as RulesAccountCreationRequestToken;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CreationRequestTokenController extends Controller
{
    public function check(Request $request, string $creationRequestToken)
    {
        $request->merge(['account_creation_request_token' => $creationRequestToken]);
        $request->validate([
            'account_creation_request_token' => [
                'required',
                new RulesAccountCreationRequestToken
            ]
        ]);

        $accountCreationRequestToken = AccountCreationRequestToken::where('token', $request->get('account_creation_request_token'))->firstOrFail();

        return view('account.creation_request_token.check', [
            'account_creation_request_token' => $accountCreationRequestToken
        ]);
    }

    public function validateToken(Request $request)
    {
        $request->validate([
            'account_creation_request_token' => [
                'required',
                new RulesAccountCreationRequestToken
            ],
            'g-recaptcha-response'  => captchaConfigured() ? 'required|captcha' : '',
        ]);

        $accountCreationRequestToken = AccountCreationRequestToken::where('token', $request->get('account_creation_request_token'))->firstOrFail();
        $accountCreationRequestToken->validated_at = Carbon::now();
        $accountCreationRequestToken->save();

        return view('account.creation_request_token.valid');
    }
}
